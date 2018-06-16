<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */
class GetNewSeedV2 extends CFormModel
{
	public $userToken;

	public $emailHashes,$limit,$lastIdKey;

	public $emailData;


	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),

			array('emailHashes', 'chkEmailHashes','on'=>'getData'),
			array('lastIdKey', 'match', 'pattern' => "/^[a-z0-9\d]{24}$/i", 'allowEmpty' => true, 'on' => 'getData','message'=>'fld2upd'),
			//
			array('limit', 'numerical', 'integerOnly' => true, 'min'=>1, 'max'=>50, 'on' => 'getData','tooSmall'=>'limitWrong','tooBig'=>'limitWrong'),
			// username and password are required

			//getNewMeta
			array('emailData', 'chkEmailData','on'=>'getNewMeta'),

			//array('hashes', 'checkHashes', 'on' => 'getNewSeedsData'),

		);
	}

	public function chkToken(){
		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}

	public function chkEmailData(){
		//verify correct data for fetching meta
		if($emails=json_decode($this->emailData,true)){
			foreach($emails as $emailId=>$modKey){

				if(strlen($emailId)>128 || !ctype_xdigit($emailId)){
					$this->addError('emailId', 'notValid');
				}
				if((strlen($modKey)!=32 && strlen($modKey)!=128) || !ctype_xdigit($modKey)){
					$this->addError('modKey', 'notValid');
				}

			}
		}else{
			$this->addError('emailData', 'notJson');
		}

	}

	public function getNewMeta()
	{
		$result['response']='success';

		if($emails=json_decode($this->emailData,true)){
			foreach($emails as $emailId=> $modKey)
				$mngData[]=array('oldId'=>$emailId,'modKey'=>hash('sha512',$modKey));


			$mngDataAgregate=array('$or'=>$mngData);

			if($ref=Yii::app()->mongo->findAll('mailQueue',$mngDataAgregate,array('_id'=>1,'meta'=>1,'pass'=>1,'oldId'=>1,'emailSize'=>1))){

				foreach($ref as $id=>$data){
					if(strlen($data['oldId'])!=24){
						$result['data'][$data['oldId']]=array(
							'metaId'=>$data['_id'],
							'oldId'=>$data['oldId'],
							'meta'=>base64_encode(substr($data['meta']->bin,0,16)).';'.base64_encode(substr($data['meta']->bin,16)),
							'pass'=>$data['pass'],
							'emailSize'=>$data['emailSize']


						);
					}else{
						$result['data'][$data['oldId']]=array(
							'metaId'=>$data['_id'],
							'oldId'=>$data['oldId'],
							'meta'=>$data['meta'],
							'pass'=>$data['pass'],
							'emailSize'=>$data['emailSize']


						);
					}

					//print_r($data['meta']);
				}


			}else{
				$result['data']=array();
			}



		}
		echo json_encode($result);
	}

	public function chkEmailHashes(){
		//make sure no longer 10 digs, and tot count not more than plan gives

		if($emails10=json_decode($this->emailHashes,true)){

			$v1=true;

			$v1Count=0;
			if(isset($emails10['v1'])){
				$v1Count=count($emails10['v1']);
				foreach($emails10['v1'] as $emhash10){
					if (strlen($emhash10) != 10 || !ctype_xdigit($emhash10)) {
						$v1=false;
					}
				}
				if(!$v1){
					$this->addError('hashV1', 'hash is wrong');
				}
			}

			$v2=true;
			$v2Count=0;
			if(isset($emails10['v2'])){
				$v2Count=count($emails10['v2']);
				foreach($emails10['v2'] as $emhash10){
					if (strlen($emhash10) != 10 || !ctype_xdigit($emhash10)) {
						$v2=false;
					}
				}
				if(!$v2){
					$this->addError('hashV2', 'hash is wrong');
				}

			}else{
				$this->addError('newKeysEmpty', 'please upgrade plan');
			}

			//$param[':userId']=Yii::app()->user->getId();
			$userId=Yii::app()->user->getId();

			if($objects = Yii::app()->mongo->findById('user', $userId, array('planData' => 1,'pastDue'=>1))){

				$limits = json_decode($objects['planData'], true);

				$totalEmailsAllowed=$limits['alias']+ $limits['dispos']+1;//add main account

				//todo enable when payment ready
				//if($v1Count>$totalEmailsAllowed || $v2Count>$totalEmailsAllowed){
				//	$this->addError('emalsCount', 'too many Keys, upgrade plan');
				//}
			}

			}else{
			$this->addError('emailData', 'notJson');
		}

	}



	public function getData()
	{
		$result['response']='success';
		$userId=Yii::app()->user->getId();

		$currentData=Yii::app()->mongo->findById('user',$userId,array('balance'=>1,'pastDue'=>1,'lastIdChecked'=>1));
/*
		if($currentData['pastDue']===2){

			$result['response']='fail';
			$result['data']='pastDue';

		}else */
            if($emails1=json_decode($this->emailHashes,true)){

			if(isset($emails1['v2'])){
				foreach($emails1['v2'] as $i=> $hash)
						$mngData[]=array('rcpnt'=>$hash);


				if(!empty($this->lastIdKey)){

					$mngDataAgregate=array("_id"=>array('$gt'=>new MongoId($this->lastIdKey)),'$or'=>$mngData);

					if($ref=Yii::app()->mongo->findAll('mailQv2',$mngDataAgregate,array('_id'=>1,'meta'=>1,'rcpnt'=>1,'file'=>1,'emailSize'=>1,'timeSent'=>1),$this->limit)){
						foreach($ref as $id=>$data){
							$result['data']['v2'][$id]=$data;
						}

						reset($ref);
						$first=key($ref);

						$result['data']['lastId']=$first;
					}


				}else if(isset($currentData['lastIdChecked'])){

					$mngDataAgregate=array("_id"=>array('$gt'=>new MongoId($currentData['lastIdChecked'])),'$or'=>$mngData);

					if($ref=Yii::app()->mongo->findAll('mailQv2',$mngDataAgregate,array('_id'=>1,'meta'=>1,'rcpnt'=>1,'file'=>1,'emailSize'=>1,'timeSent'=>1),$this->limit)){
						foreach($ref as $id=>$data){
							$result['data']['v2'][$id]=$data;
						}

							reset($ref);
						$first=key($ref);

						$result['data']['lastId']=$first;
					}

				}else{
					$mngDataAgregate=array('$or'=>$mngData);


					if($ref=Yii::app()->mongo->findAll('mailQv2',$mngDataAgregate,array('_id'=>1,'meta'=>1,'rcpnt'=>1,'file'=>1,'emailSize'=>1,'timeSent'=>1),$this->limit)){
						foreach($ref as $id=>$data){
							$result['data']['v2'][$id]=$data;
						}

						reset($ref);
						$first=key($ref);

						$result['data']['lastId']=$first;
					}


				}

				if(isset($result['data']['lastId'])){
					$user=array(
						"lastIdChecked"=>$result['data']['lastId']
					);

					$criteria=array("_id" => new MongoId($userId));

					Yii::app()->mongo->update('user',$user,$criteria);
				}


			}

		}
		echo json_encode($result);

	}



}
