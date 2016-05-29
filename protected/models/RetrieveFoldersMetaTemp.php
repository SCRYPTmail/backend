<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class RetrieveFoldersMetaTemp extends CFormModel
{

	public $messageIds,$userToken;


	public function rules()
	{
		return array(
			// username and password are required
			//array('userToken', 'unsafe', 'on'=>"importingData"),
			array('userToken', 'chkToken', 'on'=>"importingData"),
			array('messageIds', 'checkArray', 'on'=>"importingData"),
			//	array('mailHash', 'numerical','integerOnly'=>true,'allowEmpty'=>true),
		);
	}

	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}

	public function checkArray()
	{
		try {
			$this->messageIds = json_decode($this->messageIds);
		} catch (Exception $e) {
			$this->addError('message', 'Messages should be in an array');
		}

		if (is_array($this->messageIds)) {
			foreach ($this->messageIds as $row) {
				if (!is_numeric($row) && (!ctype_xdigit($row) || strlen($row)!=24)){
					$this->addError('message', 'Message ids incorrect');
					return false;
				}
			}
			return true;
		} else {
			$this->addError('message', 'Messages should be in an array');
		}
	}

	public function getData()
	{
		foreach ($this->messageIds as $i => $row) {
			if (is_numeric($row)){
				$f[$i] = $row;
				$mongof[]=new MongoId(substr(hash('sha1',$row),0,24));
				$refMong[substr(hash('sha1',$row),0,24)]=$row;
			}else if(ctype_xdigit($row) && strlen($row)==24){
				$mongof[]=new MongoId($row);
				$refMong[$row]=$row;
			}
		}
			if(isset($f))
			{
			if(MongoMigrate::movePersonalFolders($f))
				{
					if($ref=Yii::app()->mongo->findByManyIds('personalFolders',$mongof,array('_id'=>1,'body'=>1,'')))
					{
							foreach($ref as $doc)
							{
								$vect=substr($doc['body']->bin,0,16);
								$data=substr($doc['body']->bin,16);
								$row=base64_encode($vect).';'.base64_encode($data);
								$result['results'][]=array('messageHash'=>$refMong[$doc['_id']],'body'=>$row);
							}
						$resp['response']="success";
						$resp['data']=$result;
						echo json_encode($resp);
						
					}else{
					echo '{"results":"empty"}';	
					}
					
				}
			}else if(isset($mongof)){
				if($ref=Yii::app()->mongo->findByManyIds('personalFolders',$mongof,array('_id'=>1,'body'=>1,'')))
					{
							foreach($ref as $doc)
							{
								$vect=substr($doc['body']->bin,0,16);
								$data=substr($doc['body']->bin,16);
								$row=base64_encode($vect).';'.base64_encode($data);
								$result['results'][]=array('messageHash'=>$refMong[$doc['_id']],'body'=>$row);
							}
						$resp['response']="success";
						$resp['data']=$result;
						echo json_encode($resp);
						
					}else{
					echo '{"results":"empty"}';	
					}
			}else{
				echo '{"results":"empty"}';	
			}


	//	if ($result['results'] = Yii::app()->db->createCommand("SELECT messageHash,body FROM personalFolders WHERE messageHash IN ($params)")->queryAll()) {
		//	echo json_encode($result);
	//	} else
	//		echo '{"results":"empty"}';

	}

	public function getMeta()
	{

		$resp['response']="success";
		$resp['data']=array();



		if(count($this->messageIds)>0){

			foreach ($this->messageIds as $i => $row) {
				if (is_numeric($row)){ //compatibility with old messageids=numeric
					$f[$row] = $row;
					$mongof[]=new MongoId(substr(hash('sha1',$row),0,24));
					$refMong[substr(hash('sha1',$row),0,24)]=$row;
				}else if(ctype_xdigit($row) && strlen($row)==24) //if new messageid=hex
				{
					$mongof[]=new MongoId($row);
					$refMong[$row]=$row;
				}
			}

			if(isset($f))
			{
				//if old messages will try to move into mongo before fetching
				$MongoMigrate = new MongoMigrate();


				if($MongoMigrate->movePersonalFolders($f))
				{
					if($ref=Yii::app()->mongo->findByManyIds('personalFolders',$mongof,array('_id'=>1,'meta'=>1,'emailSize'=>1,'file'=>1)))
					{
						foreach($ref as $doc){

							$vect=substr($doc['meta']->bin,0,16);
							$data=substr($doc['meta']->bin,16);
							$row=base64_encode($vect).';'.base64_encode($data);
							$fileSize=0;
							if(!empty($doc['file'])){

								$files=json_decode($doc['file'],true);

								foreach($files as $fileName){
									//$fileSize+=FileWorkerV2::getFileSize($fileName);
									$fileSize=0;
								}
							}
							if(is_numeric($refMong[$doc['_id']])){
								$result['results'][]=array('messageHash'=>$refMong[$doc['_id']],'newHash'=>$doc['_id'],'meta'=>$row,'emailSize'=>$doc['emailSize'],'fileSize'=>$fileSize);
							}else{
								$result['results'][]=array('messageHash'=>$refMong[$doc['_id']],'meta'=>$row,'emailSize'=>$doc['emailSize'],'fileSize'=>$fileSize);
							}

						}
						$resp['data']=$result;
						//$resp['data']=array();
					}
				}

			}else if(isset($mongof))
			{
				//if new ids just fetch
				if($ref=Yii::app()->mongo->findByManyIds('personalFolders',$mongof,array('_id'=>1,'meta'=>1,'emailSize'=>1,'file'=>1)))
				{
					foreach($ref as $doc){

						$vect=substr($doc['meta']->bin,0,16);
						$data=substr($doc['meta']->bin,16);
						$row=base64_encode($vect).';'.base64_encode($data);

						$fileSize=0;
						if(!empty($doc['file'])){
							$files=json_decode($doc['file'],true);

							foreach($files as $fileName){
								//$fileSize+=FileWorkerV2::getFileSize($fileName);
								$fileSize=0;
							}
						}

						$result['results'][]=array('messageHash'=>$refMong[$doc['_id']],'meta'=>$row,'emailSize'=>$doc['emailSize'],'fileSize'=>$fileSize);
					}
					$resp['data']=$result;
					//$resp['data']=array();

				}

			}

		}
		//print_r($resp);
		echo json_encode($resp);
	}


}
