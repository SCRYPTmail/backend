<?php
/**
 * Author: Sergei Krutov
 * Date: 6/13/15
 * For: SCRYPTmail.com.
 * Version: RC 0.99
 */

class Google2FactV2 extends CFormModel
{

	public $secret;
	public $verificationCode;
	public $userToken;
	public $fac2Type;



	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),

			array('secret', 'match', 'pattern' => "/^[a-z0-9-\d]{29}$/i", 'allowEmpty' => false, 'on' => 'verifySecret','message'=>'scrtInvld2FacGoogle'),
			array('verificationCode', 'match', 'pattern' => "/^[0-9\d]{6}$/i", 'allowEmpty' => false, 'on' => 'verifySecret','message'=>'vrfCodeInvld2FacGoogle'),
			array('fac2Type','unsafe')

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
		$secret=$this->secret;
		$code=$this->verificationCode;

		$g = new GoogleAuthenticator();

		if ($g->checkCode($secret,$code)) {
			echo 'true';
		} else {
			echo 'false';
		}

	}



}