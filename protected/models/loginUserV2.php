<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 05/23/15
 * Time: 3:28 PM
 */
class LoginUserV2 extends CFormModel
{
	public $username;
	public $password;
	public $password2step;
	public $rememberMe;
	public $factor2;

	private $_identity;

	public function rules()
	{
		return array(
			// username and password are required
			//array('username', 'email','allowEmpty'=>false,'message'=>'emNotValid'),
			array('username,password,password2step', 'match', 'pattern'=>'/^([a-z0-9_])+$/','message'=>'pasNotValid'),
			array('username,password,password2step','length', 'min' => 128, 'max'=>128,'tooShort'=>'pasNotValid','tooLong'=>'pasNotValid'),
			//get2Fac
			array('factor2','safe')
			// rememberMe needs to be a boolean
			//array('username', 'allowedDomain'),
			// password needs to be authenticated
			//array('password', 'authenticate'),
		);
	}

	public function authenticate($attribute, $params)
	{
		if (!$this->hasErrors()) {
			$this->_identity = new UserIdentity($this->username, $this->password);
			if (!$this->_identity->authenticate()) {

				$this->_identity = new UserIdentity($this->username, $this->newPassword);
				if (!$this->_identity->authenticate()) {
					$this->username = str_replace('@scryptmail.com', '', $this->username);
					$this->addError('username', 'Incorrect username or password.');
				}
			}
		}
	}


	public function login()
	{
		$result['response']='fail';
		$tryLogin=false;
		$steps=0;

		if ($this->_identity === null)
		{
			$this->_identity = new UserIdentity($this->username, $this->password);
			$this->_identity->authenticate();
			//print_r('ssss');
			//$steps=1;

		}

		if (!$this->_identity->authenticate())
		{
			$this->_identity = new UserIdentity($this->username, $this->password2step);
			$this->_identity->authenticate();
			//print_r('7777');
			//$steps=2;
		}

		$param[':mailHash']=$this->username;

		$mngData=array('mailHash'=>$this->username);
		if($user=Yii::app()->mongo->findOne('user',$mngData,array('2ndType'=>1,'authSecret'=>1))) {
			$fac2=$user['2ndType'];

			if($fac2==0){
				//factor is desabled
				$tryLogin=true;

			}else if($fac2==1 && $this->factor2==''){
				$tryLogin=false;
				$result['response']='success';
				$result['data']='needGoogle';
			}else if($fac2==1 && $this->factor2!=''){
				$result['response']='success';

				$param[':mailHash']=$this->username;


					$authSecret=base64_decode($user['authSecret']);

					$secret=$authSecret;
					$code=$this->factor2;

					$g = new GoogleAuthenticator();

					if ($g->checkCode($secret,$code)) {
						$tryLogin=true;
					} else {
						//echo 'false';
						$result['data']='pinWrong';
					}

			}else if($fac2==2 && $this->factor2==''){
				$tryLogin=false;
				$result['response']='success';
				$result['data']='needYubi';
			}else if($fac2==2 && $this->factor2!=''){
				$result['response']='success';

				$authSecret=base64_decode($user['authSecret']);

				if($authSecret==substr($this->factor2,0,12)){

					$otp =$this->factor2;
					//print_r($otp);
					# Generate a new id+key from https://upgrade.yubico.com/getapikey
					$yubi = new Auth_Yubico(Yii::app()->params['YuserID'], Yii::app()->params['Ypass'],1,1);
					$auth = $yubi->verify($otp);
					//print_r($auth);

					if (PEAR::isError($auth)) {
						$result['data']='pinWrong';
					} else {
						$tryLogin=true;
					}
				}else{
					$result['data']='pinWrong';
				}

			}

		}else{
			$tryLogin=true;
		}



		if ($tryLogin && $this->_identity->errorCode === UserIdentity::ERROR_NONE) {
			$duration = $this->rememberMe ? 3600 * 24 * 30 : 0; // 30 days
			Yii::app()->user->login($this->_identity, $duration);

			$id=Yii::app()->user->getId();
			Yii::app()->session->deleteOldUserSessions($id);
			Yii::app()->session->updateFolderObject($id);


			$result['response']='success';
			$result['data']['status']='welcome';
			$result['data']['userId']=$id;
			//print_r(Yii::app()->user->getVersion());
			$result['data']['userObjectVersion']=Yii::app()->user->getVersion();
			$GetUserDataV2 = new GetUserDataV2();
			$result['data']['salt']=$GetUserDataV2->getUserSalt($id);

			$UserLoginTokenV2 = new UserLoginTokenV2();
			$result['data']['token']=$UserLoginTokenV2->generateUserLoginToken();

			$steps=$GetUserDataV2->getUserOneStep($id);;

			if($steps==0){
				$result['data']['oneStep']=false;
			}
			if($steps==1){
				$result['data']['oneStep']=true;
			}

			$stats=new StatsV2;
			$stats->counter('successLogin');

			if(strlen($id)==24){
				$userObj['active']= new MongoDate(strtotime('now'));

				$criteria=array("_id" => new MongoId($id));
				Yii::app()->mongo->update('user',$userObj,$criteria);
			}




		} else{
			$stats=new StatsV2;
			$stats->counter('failedLogin');
			if(isset($result['data']) && $result['data']!=='needYubi' && $result['data']!=='needGoogle'){
				$result['response']='fail';
			}

		}
			//$result['response']='fail';


	echo json_encode($result);
		//Yii::app()->user->logout();
	}


	public function get2Fac()
	{
		$result['response']='fail';
		if ($this->_identity === null)
		{
			$this->_identity = new UserIdentity($this->username, $this->password);
			$this->_identity->authenticate();

		}

		if (!$this->_identity->authenticate())
		{
			$this->_identity = new UserIdentity($this->username, $this->password2step);
			$this->_identity->authenticate();
		}

		if ($this->_identity->errorCode === UserIdentity::ERROR_NONE) {
			$result['response']='success';

			$param[':mailHash']=$this->username;

			//print_r($param);

			if($fac2=Yii::app()->db->createCommand("SELECT 2ndType FROM user WHERE mailHash=:mailHash")->queryScalar($param,true)){
				$result['data']=$fac2;

			}

		}

		echo json_encode($result);

	}


}