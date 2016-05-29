<?php
/**
 * Author: Sergei Krutov
 * Date: 6/13/15
 * For: SCRYPTmail.com.
 * Version: RC 0.99
 */

class YubiKey2FactV2 extends CFormModel
{

	public $secret;
	public $verificationCode;
	public $userToken;

	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),

			array('secret','safe'),
			array('verificationCode','safe')

		);
	}

	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}


	public function verifyCode($userId)
	{

		$otp =$this->verificationCode;
		# Generate a new id+key from https://upgrade.yubico.com/getapikey
		$yubi = new Auth_Yubico(Yii::app()->params['YuserID'], Yii::app()->params['Ypass'],1,1);
		$auth = $yubi->verify($otp);
		if (PEAR::isError($auth)) {
			echo 'false';
		} else {
			echo 'true';
		}

	}



}