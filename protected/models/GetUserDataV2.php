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
		if(Yii::app()->user->getVersion()==1){
			return Yii::app()->db->createCommand("SELECT saltS FROM user WHERE id=$id")->queryScalar();
		}else if(Yii::app()->user->getVersion()==2) {

			$salt = Yii::app()->mongo->findById('user', $id, array('saltS' => 1));
				return $salt['saltS'];
            }
	}


	public function getUserOneStep($id)
	{
		if(Yii::app()->user->getVersion()==1){
			return Yii::app()->db->createCommand("SELECT oneStep FROM user WHERE id=$id")->queryScalar();
		}else if(Yii::app()->user->getVersion()==2) {
			$oneStep = Yii::app()->mongo->findById('user', $id, array('oneStep' => 1));
			return $oneStep['oneStep'];
		}
	}

	//updating profile from version 1
	//todo remove when migration is over
	public function getRawUserObjects($userId)
	{

		if ($data=Yii::app()->db->createCommand("SELECT profileSettings,userObj,folderObj,contacts,blackList,saltS FROM user WHERE id=$userId")->queryRow()) {

			$planId=Yii::app()->db->createCommand("SELECT groupId FROM user_groups WHERE userId=$userId")->queryScalar();
			$userPlan=Yii::app()->db->createCommand("SELECT * FROM groups_definition WHERE id=$planId")->queryRow();

			$result['response']='success';
			$result['data']['profileObj']=$data['profileSettings'];
			$result['data']['userObj']=$data['userObj'];
			$result['data']['folderObj']=$data['folderObj'];
			$result['data']['contactObj']=$data['contacts'];
			$result['data']['blackObj']=$data['blackList'];
			$result['data']['salt']=$data['saltS'];
			$result['data']['oldPlan']=$userPlan;
		}else
			$result['response']='fail';

		$res=json_encode($result);

		echo $res;

	}
	/*
	public function verifyToken()
	{

		if ($salt=Yii::app()->db->createCommand("SELECT id FROM invites WHERE invitationCode=:invitationToken AND registered IS NULL")->queryRow(true, array(':invitationToken'=>$this->invitationToken))) {
			echo 'true';
		}else
			echo 'false';

	}*/


}