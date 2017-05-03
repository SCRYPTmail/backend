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

			$mngData=array('mailHash'=>$username);
			if($user=Yii::app()->mongo->findOne('user',$mngData,array('mailHash'=>1,'_id'=>1,'oldId'=>1,'password'=>1,'saltS'=>1,'version'=>1,'backVersion'=>1))) {
				return $user;
			}


	}


	public function getRole($id)
	{

        if($plan=Yii::app()->mongo->findById('user',$id,array('planData'=>1,'pastDue'=>1))) {
            $result['plan']=json_decode($plan['planData']);
            $result['isDue']=$plan['pastDue'];
            return $result;
        }

	}
}
