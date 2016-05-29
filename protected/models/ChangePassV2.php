<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class ChangePassV2 extends CFormModel
{
	public $userToken;

	public $oldPass;
	public $newPass;
	public $modKey;
	public $userObj,$oneStep;

	//googleAuth
	public $profObj,$secret,$type;

	//public $pass;

	//public $mailHash;
	//public $tokenHash;
	//public $tokenAesHash;



	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),

			array('oldPass,newPass', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'changeLoginPass'),
			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'changePassOneStep,changeLoginPass,changeSecondPass,saveGoogleAuth','message'=>'fld2upd'),



			//changeSecondPass
			array('oneStep','boolean','allowEmpty'=>false,'on'=>'changeSecondPass'),

			array('oldPass,newPass', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => true, 'on' => 'changeSecondPass'), //isset if disabling second pass


			array('userObj', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'changeSecondPass','message'=>'fld2upd'),
			array('userObj','length', 'max'=>3000000,'min'=>20,'on'=>'changeSecondPass','message'=>'fld2upd'),



			array('profObj', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'saveGoogleAuth','message'=>'fld2upd'),
			array('profObj','length', 'max'=>3000000,'min'=>20,'on'=>'saveGoogleAuth','message'=>'fld2upd'),


			array('secret', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",=;\d]+$/i", 'allowEmpty' => true, 'on' => 'saveGoogleAuth','message'=>'fld2upd'),
			array('secret','length', 'max'=>200,'min'=>10, 'allowEmpty' => true,'on'=>'saveGoogleAuth','message'=>'fld2upd'),

			array('type', 'safe','on'=>'saveGoogleAuth'),



			//changePassOneStep
			array('oldPass,newPass', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'changePassOneStep'),

			array('userObj', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'changePassOneStep','message'=>'fld2upd'),
			array('userObj','length', 'max'=>3000000,'min'=>20,'on'=>'changePassOneStep','message'=>'fld2upd'),

			array('oneStep', 'safe','on'=>'changePassOneStep'),


			//array('mailHash, pass,tokenHash','length', 'min' => 128, 'max'=>128,'on'=>'verifyPass'),

		);
	}

	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}

	public function saveGoogleAuth($userId){

		//if($object=Yii::app()->db->createCommand("SELECT profileSettings FROM user WHERE id=$userId")->queryScalar()){

		$result['response']='fail';

		if ($object = Yii::app()->mongo->findByUserIdNew('userObjects', $userId, array('profileSettings' => 1))) {

			$submitedObject=json_decode($this->profObj,true);
			$objectDecoded=json_decode($object[0]['profileSettings']->bin,true);


			if($objectDecoded[0]['nonce']<$submitedObject[0]['nonce'] && $objectDecoded[0]['hash']!=$submitedObject[0]['hash']){
				//echo '1';

				$userObj=array(
					"authSecret"=>$this->secret,
				);
				if($this->type=='google'){
					$userObj['2ndType']=1;
				}else if($this->type=='yubi'){
					$userObj['2ndType']=2;
				}else{
					$userObj['2ndType']=0;
				}

				//check if user know correct modKey
				$criteria=array("_id" => new MongoId($userId),'modKey'=>hash('sha512',$this->modKey));

				if($user=Yii::app()->mongo->update('user',$userObj,$criteria))
				{
					$profObj=array(
						"profileSettings"=>new MongoBinData($this->profObj, MongoBinData::GENERIC),
					);
					$criteria=array("userId" => $userId);

					if($prof=Yii::app()->mongo->update('userObjects',$profObj,$criteria))
					{
						$result['response']='success';
						$result['data']='saved';

					}

				}

			}else if($objectDecoded[0]['nonce']>=$submitedObject[0]['nonce']){
				$result['response']='success';
				$result['data']='newerFound';

			}else if($objectDecoded[0]['hash']==$submitedObject[0]['hash'] ){
				$result['response']='success';
				$result['data']='nothingUpdt';
			}
		}

		echo json_encode($result);



	}
	public function changePassOneStep($userId){

		$result['response']='fail';

		$criteria=array('_id'=>new MongoId($userId),'modKey'=>hash('sha512',$this->modKey));

		if ($password = Yii::app()->mongo->findOne('user',$criteria,array('password'=>1))) {
			if ($userObj = Yii::app()->mongo->findByUserIdNew('userObjects', $userId, array('userObj' => 1))) {

				if($password['password']==crypt($this->oldPass,$password['password']))
				{
					$submitedObject=json_decode($this->userObj,true);
					$objectDecoded=json_decode($userObj[0]['userObj']->bin,true);

					if($objectDecoded[0]['nonce']<$submitedObject[0]['nonce'] && $objectDecoded[0]['hash']==$submitedObject[0]['hash']){

						//echo '1';

						$Crawler=new CrawlerV2();
						$salt=base64_encode(($Crawler->makeModKey(10)));

						$user=array(
							"password"=>crypt($this->newPass,'$6$'.$salt.'$'),
						);
						$criteria=array("_id" => new MongoId($userId),'modKey'=>hash('sha512',$this->modKey));

						if($prof=Yii::app()->mongo->update('user',$user,$criteria))
						{
							$userObj=array(
								"userObj"=>new MongoBinData($this->userObj, MongoBinData::GENERIC),
							);
							$criteria=array("userId" => $userId);

							if($prof=Yii::app()->mongo->update('userObjects',$userObj,$criteria))
							{
								$result['response']='success';
								$result['data']='saved';
							}
						}

					}else if($objectDecoded[0]['nonce']>=$submitedObject[0]['nonce']){
						$result['response']='fail';
						$result['data']='newerFound';

					}

				}


			}
		}

		echo json_encode($result);


	}


	public function changeSecondPass($userId)
	{

		$result['response']='fail';


		$criteria=array('_id'=>new MongoId($userId),'modKey'=>hash('sha512',$this->modKey));


		if ($password = Yii::app()->mongo->findOne('user',$criteria,array('password'=>1))) {

			if ($userObj = Yii::app()->mongo->findByUserIdNew('userObjects', $userId, array('userObj' => 1))) {

				$submitedObject=json_decode($this->userObj,true);
				$objectDecoded=json_decode($userObj[0]['userObj']->bin,true);


				if($objectDecoded[0]['nonce']<$submitedObject[0]['nonce'] && $objectDecoded[0]['hash']==$submitedObject[0]['hash']){

					if(!empty($this->newPass)){


						if($password['password']==crypt($this->oldPass,$password['password']))
						{
							$Crawler=new CrawlerV2();
							$salt=base64_encode(($Crawler->makeModKey(10)));

							$user=array(
								"oneStep"=>$this->oneStep==1?1:0,
								"password"=>crypt($this->newPass,'$6$'.$salt.'$')
							);

							$criteria=array("_id" => new MongoId($userId),'modKey'=>hash('sha512',$this->modKey));

							if($prof=Yii::app()->mongo->update('user',$user,$criteria))
							{
								$userObj=array(
									"userObj"=>new MongoBinData($this->userObj, MongoBinData::GENERIC),
								);
								$criteria=array("userId" => $userId);
								if($prof=Yii::app()->mongo->update('userObjects',$userObj,$criteria))
								{
									$result['response']='success';
									$result['data']='saved';
								}
							}

						}else{
							$result['response']='fail';
							$result['data']='wrongPass';
						}


					}else{

							$userObj=array(
								"userObj"=>new MongoBinData($this->userObj, MongoBinData::GENERIC),
							);
							$criteria=array("userId" => $userId,'modKey'=>hash('sha512',$this->modKey));
							if($prof=Yii::app()->mongo->update('userObjects',$userObj,$criteria))
							{
								$result['response']='success';
								$result['data']='saved';
							}


					}


				}else if($objectDecoded[0]['nonce']>=$submitedObject[0]['nonce']){
					$result['response']='success';
					$result['data']='newerFound';

				}


			}
		}


		echo json_encode($result);

	}


	public function changeLoginPass($userId)
	{
		$result['response']='fail';
		$criteria=array('_id'=>new MongoId($userId));

		if ($password = Yii::app()->mongo->findOne('user',$criteria,array('password'=>1))) {
			if($password['password']==crypt($this->oldPass,$password['password']))
			{
				$Crawler=new CrawlerV2();
				$salt=base64_encode(($Crawler->makeModKey(10)));


				$user=array(
					"password"=>crypt($this->newPass,'$6$'.$salt.'$')
				);

				if($prof=Yii::app()->mongo->update('user',$user,$criteria))
				{
					$result['response']='success';
				}

			}else{
				$result['response']='fail';
				$result['data']='wrongPass';
			}

		}

		echo json_encode($result);

	}

}
