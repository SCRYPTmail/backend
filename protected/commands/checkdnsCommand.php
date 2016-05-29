<?php

class CheckdnsCommand extends CFormModel
{
	public $mails;


	public function rules()
	{
		return array(
			array('mails', 'email', 'allowEmpty' => false),
		);
	}

	public function getNS($domain){
		$domains=array();

		if($result = dns_get_record($domain, DNS_NS)){
			foreach ($result as $row) {
				$domains[]=dns_get_record($row['target'], DNS_A)[0]['ip'];
			}
		}

		if(count($domains)>0){
			return $domains;
		}else{
			return false;
		}

	}
	public function run()
	{
		$param[':time'] = date("Y-m-d H:i:s",strtotime('-15 minutes'));

		if ($verifiedDomains = Yii::app()->db->createCommand("
				SELECT id,domain,shaDomain,vrfString,pending,dnsFail FROM virtual_domains
				WHERE lastTimeChecked<:time AND globalDomain=0 LIMIT 10")->queryAll(true,$param)) {

			foreach ($verifiedDomains as $row) {
				$this->checkMX($row);
			}
		}
	}


	public function checkMX($record)
	{
		//print_r($record);

		$data['avToReg']=false;
		$data['domainOwnerValid']=false;
		$data['spfRecordValid']=false;
		$data['mxRecordValid']=false;
		$data['dkimRecordValid']=false;

		$dnsFail=false;

		$validMx=array(
			'host'=>'',
			'class'=>'',
			'pri'=>'100-',
			'target'=>'custom.scryptmail.com'
		);
		$validTXT=array(
			'host'=>$record['domain'],
			'class'=>'IN',
			'txt'=>array('v=spf1 include:scryptmail.com ~all','v=spf1 include:scryptmail.com -all')
		);

/*		$validTXT=array(
			'host'=>$domain,
			'class'=>'IN',
			'txt'=>'v=spf1 include:scryptmail.com -all'
		);*/

		//'verString'=>hash('sha256',$record['vrfString']),
		$validTXTVerif=array(
			'host'=>$record['domain'],
			'class'=>'IN',
			'checkDom'=>'scryptmail',
			'verString'=>$record['vrfString'],
			'txt'=>"scryptmail=".hash('sha256',$record['vrfString'])
		);

		$dkimRecOrig='v=DKIM1; k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDTNXD2KoQUiAmAJcp05gt0dStpoiXf0xDsD6T4M/THCT461Ata4EyuYQhJHSbZ6IDvMMrkZymLYdhbgsue6YWX44UVoX1LSYKt64HaMG+H9TrEbksH6UpbYcCDKGc7cUYolrwwmUh4fxnC3x5REbpCT7FhsHj5I3D1wmid+Yj25wIDAQAB;';

		$domainArray=explode('.',$record['domain']);

		//$domain


		if($domains=$this->getNS($record['domain'])){

			$domains[]='208.67.222.222';

			$resolver = new Net_DNS2_Resolver( array('nameservers' => $domains,'cache_type'=> 'none') );
			$resolver->timeout = 1;


			if($resp = $resolver->query($record['domain'], 'MX')){


				$mxRec=$resp->answer;

				foreach($mxRec as $i=>$row) {
					//check MX
					if ($data['mxRecordValid'] === false) {
						if (
							$row->name == $record['domain'] &&
							$row->class == 'IN' &&
							$row->type == 'MX' &&
							$row->exchange == $validMx['target']
						) {
							$data['mxRecordValid'] = true;
						}
					}

				}
			}else{
				$dnsFail=true;
			}

			if(!$dnsFail && $resp = $resolver->query($record['domain'], 'TXT')){
				$spfRec=$resp->answer;

				foreach($spfRec as $i=>$row) {

					//check SPF
					if($data['spfRecordValid']===false){
						if(
							$row->name==$validTXT['host'] &&
							$row->class==$validTXT['class'] &&
							($row->text[0]==$validTXT['txt'][0] ||
								$row->text[0]==$validTXT['txt'][1])
						) {
							$data['spfRecordValid'] = true;
						}
					}

					//check Ownership
					if($data['domainOwnerValid']===false){
						if(
							$row->name==$validTXTVerif['host'] &&
							$row->class==$validTXTVerif['class'] &&
							stripos($row->text[0], $validTXTVerif['checkDom']) !== false &&
							stripos($row->text[0], $validTXTVerif['verString'])!== false

						) {
							$data['domainOwnerValid'] = true;
						}
					}


					if($data['dkimRecordValid']===false){

						$rowDkim=implode($row->text,'');

						$dkimTempRec="default._domainkey.".$record['domain'].' '.$dkimRecOrig;

						if(
							$row->type==="TXT" &&
							$row->name===$record['domain'] &&
							$rowDkim==$dkimTempRec
						){
							$data['dkimRecordValid']=true;
						}



					}

				}

				if($data['dkimRecordValid']===false){
					try
					{
						$resp = @$resolver->query("default._domainkey.".$record['domain'], 'TXT');
						$dkimRec=$resp->answer;



						foreach($dkimRec as $dkimRow){
							if(
								$dkimRow->type==="TXT" &&
								$dkimRow->name==="default._domainkey.".$record['domain'] &&
								$dkimRow->text[0]==$dkimRecOrig
							){
								$data['dkimRecordValid']=true;
							}
						}
					}
					catch(Net_DNS2_Exception $e)
					{
						$data['dkimRecordValid']=false;
					}
				}


			}else{
				$dnsFail=true;
			}
		}

		$data['avToReg']=false;


		if($dnsFail===false){

			if((int) $record['pending']===1){
				$pend=($data['domainOwnerValid']===true?0:1);
			}else{
				$pend=0;

			}
			if($data['domainOwnerValid']===true){
				$avail=1;
			}else{
				$avail=0;
			}

			$parameter[':id']=$record['id'];
			$parameter[':spfRec']=$data['spfRecordValid'];
			$parameter[':availableForAliasReg']=$avail;
			$parameter[':mxRec']=$data['mxRecordValid'];
			$parameter[':vrfRec']=$data['domainOwnerValid'];
			$parameter[':dkimRec']=$data['dkimRecordValid'];
			$parameter[':lastTimeChecked']=date('Y-m-d H:i:s',strtotime('now'));
			$parameter[':pending']=$pend;
			$parameter[':dnsFail']=0;

			//print_r($parameter);

			Yii::app()->db->createCommand("UPDATE virtual_domains SET availableForAliasReg=:availableForAliasReg,spfRec=:spfRec,mxRec=:mxRec,vrfRec=:vrfRec,lastTimeChecked=:lastTimeChecked,dkimRec=:dkimRec,dnsFail=:dnsFail,pending=:pending WHERE id=:id AND globalDomain=0")->execute($parameter);
		}else{

			$parameter[':id']=$record['id'];
			$parameter[':dnsFail']=1;
			if((int)$record['dnsFail']===1){
				$parameter[':lastTimeChecked']=date('Y-m-d H:i:s',strtotime('+1 hour'));
			}else{
				$parameter[':lastTimeChecked']=date('Y-m-d H:i:s',strtotime('now'));
			}
			$parameter[':availableForAliasReg']=0;


		//	Yii::app()->db->createCommand("UPDATE virtual_domains SET availableForAliasReg=:availableForAliasReg,dnsFail=:dnsFail,lastTimeChecked=:lastTimeChecked WHERE id=:id AND globalDomain=0")->execute($parameter);
		}

	}



}

