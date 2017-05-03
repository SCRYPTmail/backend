<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */
class MyDbHttpSession extends CDbHttpSession
{
	public function setUserId($userId)
	{
		$db = $this->getDbConnection();
		$db->setActive(true);

       $person[] = array(
            "userId" => $userId
        );

      //  print_r(Yii::app()->session->sessionID);
       // Yii::app()->end();
       // Yii::app()->mongo->insert($this->sessionTableName, $person);
    }

	public function deleteOldUserSessions($userId)
	{
		$db = $this->getDbConnection();
		$db->setActive(true);

        $criteria=array("userId" =>$userId);
        Yii::app()->mongo->removeAll($this->sessionTableName,$criteria);
	}

    public function updateFolderObject($userId)
    {
        $obj = Yii::app()->mongo->findById('user', $userId, array('backVersion' => 1));

        if(!isset($obj['backVersion']) || $obj['backVersion']===2){
            //read folderobj, write into new data and update userobj

            //1
            if ($folderObj = Yii::app()->mongo->findByUserIdNew('userObjects', $userId, array('folderObj' => 1))) {
                $folderDec = json_decode($folderObj[0]['folderObj']->bin, true);
            }
            $newFolderDoc=array();

            foreach($folderDec as $key=>$data){
                $newFolderDoc[$key]=$data;
                $newFolderDoc[$key]['userId']=$userId;
                $newFolderDoc[$key]['index']=(int)$newFolderDoc[$key]['index'];
            }

            //2
            Yii::app()->mongo->insert('folderObj', $newFolderDoc);

            //3
            $profObj=array(
                "backVersion"=>3
            );

            $criteria=array("_id" => new MongoId($userId));

            if($message=Yii::app()->mongo->update('user',$profObj,$criteria))
            {
                $unset=array("folderObj"=>1);
                $criteria=array("userId" =>$userId);
                Yii::app()->mongo->unsetField('userObjects',$unset,$criteria);
            }

        }


    }
}