<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class UserLoginTokenV2 extends CFormModel
{
	public $userToken;
	//public $userToken;

	//public function rules()
	//{
	//	return array(
	//		array('userToken', 'match', 'pattern'=>"/^[a-f0-9\d]{32}$/i"),

	//	);
	//}
	public function verifyUserLoginToken()
	{
		$secTok=Yii::app()->session['secureToken'];

		if(hash('sha512',$this->userToken)==$secTok){
			return true;
		}else{
			return false;
		}

	}

	public function generateUserLoginToken()
	{
		$secTok=bin2hex(openssl_random_pseudo_bytes(32));
		Yii::app()->session['secureToken'] = hash("sha512",$secTok);
		return $secTok;

	}

}