<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class DeleteAccountV2 extends CFormModel
{
	public $userToken,$modKey,$emails,$lockEmail;

	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),
			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false,'message'=>'fld2upd'),
			array('emails', 'match', 'pattern' => "/^[a-f0-9+{:}\",\d]+$/i", 'allowEmpty' => false,'message'=>'fld2upd'),
			array('lockEmail','boolean','allowEmpty'=>false),
			array('emails','length', 'max'=>3000000,'min'=>0,'message'=>'fld2upd'),


		);
	}

	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}

	public function removeAccount($userId)
	{

		//todo we marked for deletion, need to create crawler to completly remove

		$result['response']='success';

		if ($object = Yii::app()->mongo->findById('user', $userId, array('password' => 1))) {

			$dQueue[]=array(
				"userId"=>$userId,
				"emails"=>$this->emails,
				"lockEmail"=>$this->lockEmail,
				"modKey"=>$this->modKey
			);

			if($userPreDeletion=Yii::app()->mongo->insert('deletionQ',$dQueue))
			{
				$userObj['password']='*'.$object['password'];

				$criteria=array("_id" => new MongoId($userId),'modKey'=>hash('sha512',$this->modKey));

				if($user=Yii::app()->mongo->update('user',$userObj,$criteria))
				{

				}else{
					$result['response']='fail';

				}

			}else{
				$result['response']='fail';

			}

		}else{
			$result['response']='fail';

		}

		echo  json_encode($result);

		/*
		print_r($this->lockEmail);
*/



	/*

		$result['response']='success';
		$emails=json_decode($this->emails,true);

		//delete user
		//todo enable after checking the rest funct is working
		//$criteria=array("_id" => new MongoId($userid),"modKey"=>hash('sha512',$this->modKey));

		//if(Yii::app()->mongo->removeAll('user',$criteria)){

		//}
		//todo delete personal folder based on oldid and new one

		if (count($emails) > 0) {
			foreach($emails as $i => $modKey) {

				if(is_numeric($i))
					$mngData[]=array('_id'=>new MongoId(substr(hash('sha1',$i),0,24)),'modKey'=>hash('sha512',$modKey));
				else if(ctype_xdigit($i))
					$mngData[]=array('_id'=>new MongoId($i),'modKey'=>hash('sha512',$modKey));

			}
		}
		$mngDataAgregate=array('$or'=>$mngData);

		//todo new style for attachments look for fileToObj

		if($ref=Yii::app()->mongo->findAll('personalFolders',$mngDataAgregate,array('_id'=>1,'file'=>1))){
			foreach($ref as $doc){
				if($files=json_decode($doc['file'],true)){
					foreach($files as $names){
						FileWorks::deleteFile($names);
					}


				}
			}
		}

		//new style removing files

		$criteria=array("userId" => $userid);

		if($files=Yii::app()->mongo->findAll('fileToObj',$criteria,array('pgpFileName'=>1))){
			foreach($files as $file){
					FileWorks::deleteFile($file['pgpFileName']);
			}
		}

		Yii::app()->mongo->removeAll('fileToObj',$criteria);


		if(is_array($mngDataAgregate)){
			Yii::app()->mongo->removeAll('personalFolders',$mngDataAgregate);

		}

		//delete emailaliases /disp
		$userObj=array(
			"active"=>0,
			"expireAt"=>new MongoDate(strtotime('now'. '+ 2 month')),
			"retentionStarted"=>new MongoDate(strtotime('now'))
		);
		$criteria=array("userId" => $userid,'active'=>1);
		Yii::app()->mongo->update('addresses',$userObj,$criteria);


		$param[':userId']=$userid;

		//delete domain
		Yii::app()->db->createCommand("DELETE FROM virtual_domains WHERE userId=:userId")->execute($param);

		//delete safebox
		Yii::app()->db->createCommand("DELETE FROM safeBoxStorage WHERE userId=:userId")->execute($param);


		$criteria=array("userId" => $userid);
		Yii::app()->mongo->removeAll('userObjects',$criteria);

		//$trans->rollback();

		echo  json_encode($result);;

*/

	}


}