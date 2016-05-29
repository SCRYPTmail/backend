<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class UserV2 extends CFormModel
{

	public function findUser($username)
	{
		$param[':mailHash'] = $username;
		$user1=Yii::app()->db->createCommand("SELECT mailHash,id,password,userObj,folderObj,saltS,version FROM user WHERE mailHash=:mailHash")->queryRow(true, $param);

		if($user1['version']==1){
			return $user1;
		}else{
			$mngData=array('mailHash'=>$username);
			if($user=Yii::app()->mongo->findOne('user',$mngData,array('mailHash'=>1,'_id'=>1,'oldId'=>1,'password'=>1,'userObj'=>1,'folderObj'=>1,'saltS'=>1,'version'=>1))) {
				return $user;
			}
		}

	}


	public function getRole($id)
	{

		if(Yii::app()->user->getVersion()===2){

			if($plan=Yii::app()->mongo->findById('user',$id,array('planData'=>1,'pastDue'=>1))) {
				$result['plan']=json_decode($plan['planData']);
				$result['isDue']=$plan['pastDue'];
				return $result;
			}

		}else{
			$result['plan']=array();
			$result['isDue']=0;
			return $result;
		}

	}

	//public function getGroups($id)
	//{

	//	$data = Yii::app()->db->createCommand("SELECT * FROM userFeatures WHERE userId=$id")->queryAssoc('id');
//
	//	return $data;
	//}

}
