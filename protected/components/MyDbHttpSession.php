<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */
class MyDbHttpSession extends CDbHttpSession
{
	/*public function setUserId($userId)
	{
		$db = $this->getDbConnection();
		$db->setActive(true);

       $person[] = array(
            "userId" => $userId
        );


        Yii::app()->mongo->insert($this->sessionTableName, $person);
    }

	public function deleteOldUserSessions($userId)
	{
		$db = $this->getDbConnection();
		$db->setActive(true);

        $criteria=array("userId" =>$userId);
        Yii::app()->mongo->removeAll($this->sessionTableName,$criteria);
	}*/
}