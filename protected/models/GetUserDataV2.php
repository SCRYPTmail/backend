<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class GetUserDataV2 extends CFormModel
{

	public $userToken;
	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),

		);
	}
	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}

	/*public function getUserVersion($id)
	{
		//print_r($id);
		return Yii::app()->db->createCommand("SELECT version FROM user WHERE id=$id")->queryScalar();

	}
	*/

	public function getUserSalt($id)
	{

        $salt = Yii::app()->mongo->findById('user', $id, array('saltS' => 1));
        return $salt['saltS'];

	}


	public function getUserOneStep($id)
	{

        $oneStep = Yii::app()->mongo->findById('user', $id, array('oneStep' => 1));
        return $oneStep['oneStep'];

	}


}