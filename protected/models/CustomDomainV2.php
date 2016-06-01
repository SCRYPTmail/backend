<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class CustomDomainV2 extends CFormModel
{

	public $userToken;
	public $domain,$vrfString;



	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),

			//array('vrfString', 'match', 'pattern' => "/^[a-z0-9\d]{64}$/i", 'allowEmpty' => false, 'on' => 'addPending','message'=>'chckVrf'),

			//array('domain', 'url', 'defaultScheme' => 'http', 'on' => 'addPending','message'=>'addPending')



		);
	}

	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}


	public function vrfOwnership($userId,$domain,$vrfString)
	{
		$domain=str_replace('http://','',$domain);
		$domain=str_replace('https://','',$domain);

		$domain=str_replace('http://www.','',$domain);
		$domain=str_replace('https://www.','',$domain);

		$param[':shaDomain']=hash('sha512',$domain);
		$param[':userId']=$userId;

		if ($dd = Yii::app()->db->createCommand("SELECT * FROM virtual_domains WHERE shaDomain=:shaDomain AND userId=:userId AND globalDomain=0")->queryRow(true, $param)) {
			return 1;

		}else{
			return 2;
		}

	}


	public function addPending($userId,$domain,$vrfString)
	{
		$domain=str_replace('http://','',$domain);
		$domain=str_replace('https://','',$domain);

		$domain=str_replace('http://www.','',$domain);
		$domain=str_replace('https://www.','',$domain);

		$param[':shaDomain']=hash('sha512',$domain);

		$domainExist=false;

		if ($dd = Yii::app()->db->createCommand("SELECT * FROM virtual_domains WHERE shaDomain=:shaDomain")->queryRow(true, $param)) {
			if((int) $dd['globalDomain']===1){
				$domainExist=true;
			}

			if((int)$dd['pending']===1 || (int)$dd['obsolete']===1){

			}else{
				$domainExist=true;
			}
		}

		//unset($param);

		if(!$domainExist){

			$param[':domain']=$domain;
			$param[':availableForAliasReg']=0;
			$param[':destination']='myhook';
			$param[':vrfString']=hash('sha256',$vrfString);
			$param[':pending']=1;
			$param[':globalDomain']=0;
			$param[':userId']=$userId;
			$param[':lastModified']=date('Y-m-d H:i:s',strtotime('now'));
			$param[':lastTimeChecked']=date('Y-m-d H:i:s',strtotime('-5minutes'));

			//print_r($param);

			if(Yii::app()->db->createCommand("REPLACE INTO virtual_domains (domain,destination,shaDomain,availableForAliasReg,vrfString,pending,userId,globalDomain,lastModified,lastTimeChecked) VALUES (:domain,:destination,:shaDomain,:availableForAliasReg,:vrfString,:pending,:userId,:globalDomain,:lastModified,:lastTimeChecked)")->execute($param)){

				return 1;
				//$result['response']='success';
			}else{
				return 2;
				//$result['response']='fail';
			}
		}else{
			return 3;
			//$result['error']=['domAlrdExist'];
		}

		//echo json_encode($result);


	}

	public function deleteDomain($userId,$domain)
	{
		$domain=str_replace('http://','',$domain);
		$domain=str_replace('https://','',$domain);

		$domain=str_replace('http://www.','',$domain);
		$domain=str_replace('https://www.','',$domain);

		$param[':shaDomain']=hash('sha512',$domain);
		$param[':userId']=$userId;

		$domainExist=false;
		if ($dd = Yii::app()->db->createCommand("DELETE FROM virtual_domains WHERE shaDomain=:shaDomain AND userId=:userId")->execute($param)) {
			//$domainExist=true;

		}
		return 1;

	}


	public function retrieveDomainsForUser($userId)
	{
		$result['response']="success";
		$result['domains']=array();

		$param[':userId']=$userId;
		if ($domains = Yii::app()->db->createCommand("SELECT domain,spfRec,mxRec,vrfRec,dkimRec,pending,suspended,obsolete,availableForAliasReg FROM virtual_domains WHERE userId=:userId")->queryAll(true,$param)) {
			foreach($domains as $row){
				$result['domains'][base64_encode($row['domain'])]=$row;
				$result['domains'][base64_encode($row['domain'])]['domain']=base64_encode($row['domain']);
			}

			$param[':time'] = date("Y-m-d H:i:s",strtotime('-1 hour'));

			Yii::app()->db->createCommand("UPDATE virtual_domains SET lastTimeChecked=:time WHERE userId=:userId")->execute($param);
		}
		echo json_encode($result);

	}


public function get_domain($domain) {

	$pslManager = new PublicSuffixListManager();
	$parser = new  Parser($pslManager->getList());

	$url = $parser->parseUrl($domain);


	print_r($url->host->registrableDomain);
	/*	$original = $domain = strtolower($domain);

		if (filter_var($domain, FILTER_VALIDATE_IP)) { return $domain; }
		$arr = array_slice(array_filter(explode('.', $domain, 4), function($value){
			return $value !== 'www'; }), 0); //rebuild array indexes

		if (count($arr) > 2)    {
			$count = count($arr);
			$_sub = explode('.', $count === 4 ? $arr[3] : $arr[2]);

			if (count($_sub) === 2)  { // two level TLD
				$removed = array_shift($arr);
				if ($count === 4) // got a subdomain acting as a domain
					$removed = array_shift($arr);

			}
			else if (count($_sub) === 1){ // one level TLD
				$removed = array_shift($arr); //remove the subdomain

				if (strlen($_sub[0]) === 2 && $count === 3 && !in_array($_sub[0],Yii::app()->params['params']['sufTlds'])) { // TLD domain must be 2 letters
					array_unshift($arr, $removed);
				}else if($count > 3  && in_array($_sub[0],Yii::app()->params['params']['sufTlds'])){

				}else{
					// non country TLD according to IANA
					$tlds = Yii::app()->params['params']['tlds'];

					if (count($arr) > 2 && in_array($_sub[0], $tlds) !== false) {//special TLD don't have a country
						array_shift($arr);
					}
				}

			}
			else { // more than 3 levels, something is wrong
				for ($i = count($_sub); $i > 1; $i--)
					$removed = array_shift($arr);


			}
		}
		elseif (count($arr) === 2) {
			$arr0 = array_shift($arr);
			if (strpos(join('.', $arr), '.') === false
				&& in_array($arr[0], array('localhost','test','invalid')) === false) // not a reserved domain
			{

				// seems invalid domain, restore it
				array_unshift($arr, $arr0);
			}
		}


		return join('.', $arr);*/
	}


}