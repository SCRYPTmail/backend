<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class CheckIfExistV2 extends CFormModel
{

	public $email;

	public $userToken;
	public $password;
	public $fromEmail,$modKey;


	public $domain,$vrfString;

	public $publicKey;

	public function rules()
	{
		return array(
			//array('userToken', 'chkToken'),

			array('email', 'email', 'allowEmpty' => false, 'on' => 'email'),
			array('fromEmail,userToken', 'unsafe','on'=>'email'),

			array('password', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'password'),
			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'password','message'=>'fld2upd'),

			array('vrfString', 'match', 'pattern' => "/^[a-z0-9\d]{64}$/i", 'allowEmpty' => false, 'on' => 'domain','message'=>'chckVrf'),

			array('domain', 'url', 'defaultScheme' => 'http', 'on' => 'domain','message'=>'chkdomain'),


			array('publicKey', 'match', 'pattern' => "/^[a-zA-Z0-9+\/=\d]+$/i", 'allowEmpty' => false, 'on' => 'publicKey'),
			array('publicKey','length', 'max'=>8000,'min'=>100,'on'=>'publicKey'),


			//publicKey


		);
	}

	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}


	public function validatePassword($userId)
	{

		$criteria=array('_id'=>new MongoId($userId),'modKey'=>hash('sha512',$this->modKey));

		if ($password = Yii::app()->mongo->findOne('user',$criteria,array('password'=>1))) {

			if($password['password']==crypt($this->password,$password['password']))
			{
				$result['response']="success";
			}else{
				$result['response']="fail";
			}
		}else{
			$result['response']="fail";
		}

		echo json_encode($result);
	}


	/*public function ifKeyisUniq()
	{
		$result['response']='success';
		echo json_encode($result);

	}*/

	public function validateEmail($userId,$callback=null)
	{

		//check if previousl owned by same user or retention period expired
		$email=hash('sha512',strtolower($this->email));
		$criteria=array('addressHash'=>$email);

		if ($address = Yii::app()->mongo->findOne('addresses',$criteria,array('addressHash'=>1,'active'=>1,'userId'=>1)))
		{
			if($address['active']==0 && $address['userId']==$userId){
				$return='true';
			}else {
				$return='false';
			}

		}else{
			$param[':mailHash'] = $email;

			if (Yii::app()->db->createCommand("SELECT addressHash FROM addresses WHERE addressHash=:mailHash")->queryRow(true, $param)) {
				$return='false';
			} else
				$return='true';
		}

		if($callback===null){
			echo $return;

		}else{
			return $return;
		}

	}

	public function validateDomain($userId)
	{
		$domain = str_replace('http://', '', $this->domain);
		$domain = str_replace('https://', '', $domain);
		$result['response'] = 'true';

		$param[':shaDomain'] = hash('sha512', $domain);
		//$param[':vrfString']=hash('sha512',$domain);

		if ($domain = Yii::app()->db->createCommand("SELECT * FROM virtual_domains WHERE shaDomain=:shaDomain")->queryRow(true, $param)) {
			if((int) $domain['globalDomain']===1){
				$result['response'] = 'false';
			}
			if($domain['userId']==$userId){
				$result['response'] = 'false';
			}else if((int)$domain['pending']===1 || (int)$domain['obsolete']===1){

			}else{
				$result['response'] = 'false';
			}

		}

		echo json_encode($result);

//print_r($domain);

		//	print_r($this->vrfString);
	}


}