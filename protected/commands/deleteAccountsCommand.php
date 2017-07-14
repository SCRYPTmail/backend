<?php

class DeleteAccountsCommand extends CFormModel
{


	/**
	 * Deleting acconts from system
	 *
	 * 1) get list of accounts to delete
	 * 2) mark email addresses to retention
	 * 4) delete files associated with account
	 * 5) delete emails associated
	 * 6) delete from user table
	 */
	public function run()
	{


		//$criteria=array('userId'=>$userId,'addr_type'=>3,'active'=>1);

		//1 get list of accounts to delete
		$filesV2=array();
		$filesV1=array();

		if($acc2del=Yii::app()->mongo->findAll('deletionQ', array(),array(),1)) {

			foreach($acc2del as $ind=>$account){

				//2 mark email addresses to retention
				if((int)$account['lockEmail']===1){
					$addressObj=array(
						"active"=>0,
						"emailLocked"=>true,
					);
				}else{
					$addressObj=array(
						"active"=>0,
						"expireAfter"=>new MongoDate(strtotime('now + 6 month')),
					);
				}

				$criteria=array("userId" =>$account['userId'],'active'=>1);
				Yii::app()->mongo->update('addresses',$addressObj,$criteria,null,false,true);
				unset($criteria,$addressObj);

				$criteria=array("userId" =>$account['userId']);
					//4 a)delete new files
					if($filesa=Yii::app()->mongo->findAll('fileToObj',$criteria)){
						foreach($filesa as $emailId=>$emailData){
							$filesV2[]=$emailData;
						}

						$this->deleteV2($filesV2);

						Yii::app()->mongo->removeAll('fileToObj',$criteria);
					}
				unset($criteria);

				$emaiList=json_decode($account['emails'],true);

				if(count($emaiList)>0){
					foreach($emaiList as $emId=>$modKey){
						if(strlen($emId)===24 && isset($modKey)){
							$mngData[]=array('_id'=>new MongoId($emId),'modKey'=>hash('sha512',$modKey));
						}
					}


                    if(isset($mngData)){
                        $mngDataAgregate=array('$or'=>$mngData);

                        if($emails=Yii::app()->mongo->findAll('personalFolders',$mngDataAgregate,array('file'=>1,'v'=>1))){
                            foreach($emails as $emailId=>$emailData){

                                if((!isset($emailData['v']) || $emailData['v']!==2) && isset($emailData['file']) && $emailData['file']!=='null'){
                                    $filesV1[]=json_decode($emailData['file'],true);
                                }

                            }
                            //5) delete emails associated
                            Yii::app()->mongo->removeAll('personalFolders',$mngDataAgregate);

                            $this->deleteV1($filesV1);
                        }
                    }



				}

				//delete user

				$criteria=array('_id'=>new MongoId($account['userId']),'modKey'=>hash('sha512',$account['modKey']));
				if(Yii::app()->mongo->removeAll('user',$criteria)){
					unset($emaiList);
					//delete userObjects
					$criteria=array("userId" =>$account['userId']);
					Yii::app()->mongo->removeAll('userObjects',$criteria);

					//remove from deleteQ
					Yii::app()->mongo->removeAll('deletionQ',$criteria);
				}

				unset($emaiList,$filesV1,$filesV2,$mngDataAgregate,$mngData,$criteria);

			}
		}
	}



	function deleteV1($files){

		if(is_array($files)){

			$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
			$host = Yii::app()->params['host'];
			$folder=Yii::app()->params['folder'];
			$username = Yii::app()->params['username'];
			$password = Yii::app()->params['password'];
			$objectStorage = new ObjectStorage($host, $username, $password, $options);

			foreach($files as $messageId=>$fileArray){

				foreach($fileArray as $i=>$fName){
					$fOname=$fName;
					$objectStorage->with($folder.'/'.$fOname)->delete();

				}
			}
		}

	}

	function deleteV2($files){
		if(is_array($files)){
			$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
			$host = Yii::app()->params['host'];
			$folder=Yii::app()->params['folder'];
			$username = Yii::app()->params['username'];
			$password = Yii::app()->params['password'];
			$objectStorage = new ObjectStorage($host, $username, $password, $options);

			foreach($files as $ind=>$fileData){

				$fOname=$fileData['pgpFileName'];
				$objectStorage->with($folder.'/'.$fOname)->delete();

			}
		}

	}


}

