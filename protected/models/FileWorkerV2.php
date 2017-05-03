<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */
class FileWorkerV2 extends CFormModel
{
	public $userToken;

	public $file,$modKey,$fileName,$version,$emailId,$emailModKey;


	public $fileId, $filePass;


	public function rules()
	{
		return array(

		array('userToken', 'chkToken','except'=>'downloadFileUnreg,downloadFilePublic'),

		//saveNewAttachment
		array('file', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'saveNewAttachment','message'=>'fld2upd'),
		array('file','length', 'max'=>60000000,'allowEmpty' => true,'on'=>'saveNewAttachment','message'=>'fld2upd'),
		array('emailId', 'match', 'pattern' => "/^[a-z0-9\d]{24}$/i", 'allowEmpty' => false, 'on' => 'saveNewAttachment','message'=>'fld2upd'),
		array('emailModKey', 'match', 'pattern' => "/^[a-z0-9\d]{32}$/i", 'allowEmpty' => false, 'on' => 'saveNewAttachment','message'=>'fld2upd'),

		array('file','checkSize','on'=>'saveNewAttachment','message'=>'fld2upd'),
		array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'saveNewAttachment','message'=>'fld2upd'),


		//removeFileFromDraft
		array('fileName', 'match', 'pattern' => "/^[a-z0-9\d]{24,27}$/i", 'allowEmpty' => false, 'on' => 'removeFileFromDraft','message'=>'fld2upd'),
		array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32}$/i", 'allowEmpty' => false, 'on' => 'removeFileFromDraft','message'=>'fld2upd'),

		//downloadFile
		array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{4,32}$/i", 'allowEmpty' => true, 'on' => 'downloadFile','message'=>'fld2upd'),
		array('fileName', 'match', 'pattern' => "/^[a-z0-9\d]{24,128}$/i", 'allowEmpty' => false, 'on' => 'downloadFile','message'=>'fld2upd'),
		array('version', 'numerical', 'integerOnly' => true, 'min'=>1, 'max'=>30, 'on' => 'downloadFile','tooSmall'=>'limitWrong','tooBig'=>'limitWrong'),

		//downloadFileUnreg
		array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{4,32}$/i", 'allowEmpty' => true, 'on' => 'downloadFileUnreg','message'=>'fld2upd'),
		array('fileName', 'match', 'pattern' => "/^[a-z0-9\d]{24,128}$/i", 'allowEmpty' => false, 'on' => 'downloadFileUnreg','message'=>'fld2upd'),
		array('version', 'numerical', 'integerOnly' => true, 'min'=>1, 'max'=>30, 'on' => 'downloadFileUnreg','tooSmall'=>'limitWrong','tooBig'=>'limitWrong'),


		//downloadFilePublic
		array('fileId', 'match', 'pattern' => "/^[a-z0-9\d]{25}$/i", 'allowEmpty' => true, 'on' => 'downloadFilePublic','message'=>'fld2upd'),
		array('filePass', 'match', 'pattern' => "/^[a-z0-9\d]{64}$/i", 'allowEmpty' => false, 'on' => 'downloadFilePublic','message'=>'fld2upd'),

		);
	}

	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}
	public function checkSize(){

		$param[':userId']=Yii::app()->user->getId();


		if ($limitsJSON = Yii::app()->mongo->findById('user', Yii::app()->user->getId(), array('planData' => 1,'pastDue'=>1))) {

			$limits = json_decode($limitsJSON['planData'], true);
			if ($limitsJSON['pastDue'] > 0) {
				$this->addError('account', 'pastDue');
			}
			$sendLimits = $limits['attSize'];

			if(strlen($this->file)>($sendLimits*1024*1024*2)){
				$this->addError('fileSize', 'overLimit');
			}
		}

	}


	public function downloadPublic()
	{
		$result['response']='fail';

		$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
		$host = Yii::app()->params['host'];
		$folder=Yii::app()->params['folder'];
		$username = Yii::app()->params['username'];
		$password = Yii::app()->params['password'];

		$mngData=array('pgpFileName'=>$this->fileId,'public'=>true);

		$objectStorage = new ObjectStorage($host, $username, $password, $options);

		if($file=Yii::app()->mongo->findOne('fileToObj',$mngData)) {
			//print_r($file['_id']);
			$res = $objectStorage->with($folder . '/' . $file['_id'])->get();
			if ($res != 'not found') {
				$data = $res->getBody();
				$fileBroken = explode(';', $data);
				$iv = base64_decode($fileBroken[0]);
				$encrypted = $fileBroken[1];


				$key = hex2bin($this->filePass);
				$encryptionMethod = "aes-256-cbc";
				$g = openssl_decrypt($encrypted, $encryptionMethod, $key, 0, $iv);

				$fileT = explode(';', $file['type']);
				$fileType = openssl_decrypt($fileT[1], $encryptionMethod, $key, 0, base64_decode($fileT[0]));

				$fileN = explode(';', $file['name']);
				$fileName = openssl_decrypt($fileN[1], $encryptionMethod, $key, 0, base64_decode($fileN[0]));

				//print_r(base64_decode($fileType));

				header("Cache-Control: public");
				header("Content-Description: File Transfer");
				header("Content-Disposition: attachment; filename=" . base64_decode($fileName));
				header("Content-Type: ".base64_decode($fileType));
				header("Content-Type: application/octet-stream");
				echo base64_decode($g);
			}
		}

	}

	public function downloadFileUnreg($userId)
	{
		$result['response']='fail';

		$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
		$host = Yii::app()->params['host'];
		$folder=Yii::app()->params['folder'];
		$username = Yii::app()->params['username'];
		$password = Yii::app()->params['password'];


        $objectStorage = new ObjectStorage($host, $username, $password, $options);

        $res = $objectStorage->with($folder.'/'.$this->fileName)->get();

        if($res!='not found'){
            $result['response']='success';
            $result['data']=$res->getBody();
        }




		echo json_encode($result);
	}


	public function downloadFile($userId)
	{
		$result['response']='fail';

		$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
		$host = Yii::app()->params['host'];
		$folder=Yii::app()->params['folder'];
		$username = Yii::app()->params['username'];
		$password = Yii::app()->params['password'];


		if($this->version==="15"){
			$fOname=$this->fileName;
			$objectStorage = new ObjectStorage($host, $username, $password, $options);

			$res = $objectStorage->with($folder.'/'.$fOname)->get();
			if($res!='not found'){
				$result['response']='success';
				$result['data']=$res->getBody();
			}
		}else if($this->version==="1"){
			$fOname=hash('sha512',$this->fileName);
			$objectStorage = new ObjectStorage($host, $username, $password, $options);

			$res = $objectStorage->with($folder.'/'.$fOname)->get();
			if($res!='not found'){
				$result['response']='success';

				$data = $res->getBody();
				$iv = hex2bin(substr($data, 0, 32));
				$encrypted = substr($data, 32);

				$result['data']=base64_encode($iv).';'.$encrypted;
			}
		}else if($this->version==="2"){
			$mngData=array('pgpFileName'=>hash('sha256',$this->fileName.$userId),'modKey'=>hash('sha256', $this->modKey.$userId));
			$objectStorage = new ObjectStorage($host, $username, $password, $options);

            if($file=Yii::app()->mongo->findOne('fileToObj',$mngData)){
                $res = $objectStorage->with($folder.'/'.$file['pgpFileName'])->get();

                if($res!='not found'){
                    $result['response']='success';
                    $result['data']=$res->getBody();
                }

            }

		}

		echo json_encode($result);
	}


	/**
	 * Copy file for new email when fetching, from crawler for version 2
	 *
	 * @param $fileObject
	 * @param $userId
	 * @param null $expireAfter
	 * @return array|null|string
	 */
	public function makeFilesCopyV2($fileObject,$userId,$emailId,$emailModKey,$expireAfter=null)
	{

		$result=null;
		if(!empty($fileObject)){
			$fileObject=json_decode($fileObject,true);
		}

		if(is_array($fileObject)){

			$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
			$host = Yii::app()->params['host'];
			$folder=Yii::app()->params['folder'];
			$username = Yii::app()->params['username'];
			$password = Yii::app()->params['password'];

			$objectStorage = new ObjectStorage($host, $username, $password, $options);


			foreach($fileObject as $fileName=>$modKey){

				$mngData=array('pgpFileName'=>$fileName,'modKey'=>hash('sha256', $modKey));


				if($ref=Yii::app()->mongo->findOne('fileToObj',$mngData)) {


					//if ($ref[0]['modKey'] === hash('sha256', $modKey)) {

						$object = $objectStorage->with($folder.'/'.$fileName)->get();

						if($object!='not found'){

							$fileN=hash('sha256',$fileName.$userId);
							$fileMod=hash('sha256',$modKey.$userId);
							$mngId = new MongoId();

							$file[]=array(
								"_id"=>$mngId,
								"pgpFileName"=>$fileN,
								"emailId"=>hash('sha256',$emailId.$emailModKey),
								"userId"=>$userId,
								"modKey"=>$fileMod,
							);

							if($message=Yii::app()->mongo->insert('fileToObj',$file))
							{
								try{
									if($expireAfter===null){
										$objectStorage->with($folder.'/'.$fileN)
											->setBody($object->getBody())
											->setHeader('Content-type', 'application/octet-stream')
											->create();
									}else{
										$objectStorage->with($folder.'/'.$fileN)
											->setBody($object->getBody())
											->setHeader('Content-type', 'application/octet-stream')
											->deleteAt($expireAfter)
											->create();
									}

									$result[]=$fileN;

								} catch (Exception $e) {

								}

							}
						}
					//}else {
					//	return $result;
					//}
				}else {
					return $result;
				}

				unset($file);
			}

			return json_encode($result);
		}else {
			return $result;
		}

	}

	/**
	 * copy file for user when he fetch email for Version 1.5 transition
	 *
	 * @param $fileObject
	 * @param $userId
	 * @param null $expireAfter
	 * @return bool
	 */
	public function makeFilesCopy($fileObject,$userId,$expireAfter=null)
	{

		$result=null;
		if(!empty($fileObject)){
			$fileObject=json_decode($fileObject,true);
		}
		if(is_array($fileObject)){

			$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
			$host = Yii::app()->params['host'];
			$folder=Yii::app()->params['folder'];
			$username = Yii::app()->params['username'];
			$password = Yii::app()->params['password'];

			$objectStorage = new ObjectStorage($host, $username, $password, $options);

			foreach($fileObject as $fileName=>$modKey){

				$mongof=new MongoId(substr($fileName, 0,24));

				if($ref=Yii::app()->mongo->findByManyIds('fileToObj',array($mongof),array('_id'=>1,'modKey'=>1))) {

					if ($ref[0]['modKey'] === hash('sha256', $modKey)) {

						try{
							$object = $objectStorage->with($folder.'/'.substr($fileName, 0,24))->get();

							$newFileName=hash('sha512',$fileName.$userId);
							if($object!='not found'){
								if($expireAfter===null){
									$objectStorage->with($folder.'/'.$newFileName)
										->setBody($object->getBody())
										->setHeader('Content-type', 'application/octet-stream')
										->create();
								}else{
									$objectStorage->with($folder.'/'.$newFileName)
										->setBody($object->getBody())
										->setHeader('Content-type', 'application/octet-stream')
										//->deleteAfter(20)
										->deleteAt($expireAfter)
										->create();
								}

								$result[]=$newFileName;
							}else{
								return $result;
							}


						} catch (Exception $e) {

							return $result;
							//todo reverse if fail in the process
						}
					}else {
						return $result;
					}
				}else {
					return $result;
				}

			}
			//return new filenames
			return json_encode($result);
		}else {
			return $result;
		}

	}


	/**
	 * File Copy when send Internal email
	 * @param $fileObject
	 * @param $userId
	 * @param null $expireAfter
	 * @return bool
	 */

	public function makeCopiesWithModKeyInt($fileObject,$userId,$expireAfter=null)
	{
		//pin email
		//print_r($fileObject);
		$size=0;

		if(is_array($fileObject)){

			$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
			$host = Yii::app()->params['host'];
			$folder=Yii::app()->params['folder'];
			$username = Yii::app()->params['username'];
			$password = Yii::app()->params['password'];


			$objectStorage = new ObjectStorage($host, $username, $password, $options);

			foreach($fileObject as $fileName=>$modKey){

				$mongof=new MongoId(substr($fileName, 0,24));

				//print_r($mongof);

				if($ref=Yii::app()->mongo->findByManyIds('fileToObj',array($mongof),array('_id'=>1,'modKey'=>1))) {
					//print_r($ref);
					if ($ref[0]['modKey'] === hash('sha256', $modKey.$userId)) {

						$fileId=hash('sha256',substr($fileName, 0,24).$userId);

						try{
							$object = $objectStorage->with($folder.'/'.$fileId)->get();

							//print_r($object);

							if($object!='not found'){
								$size+=strlen($object->getBody());

								$mngId = new MongoId();
								//$fname=(string) $mngId;

								if($expireAfter===null){
									$file[]=array(
										"_id"=>$mngId,
										"pgpFileName"=>$fileName,
										"emailId"=>hash('sha256',$fileName.$modKey),
										"userId"=>0,
										"modKey"=>hash('sha256',$modKey),
									);
								}else{
									$file[]=array(
										"_id"=>$mngId,
										"pgpFileName"=>$fileName,
										"emailId"=>hash('sha256',$fileName.$modKey),
										"userId"=>0,
										"modKey"=>hash('sha256',$modKey),
										"expireAfter" => new MongoDate($expireAfter),	//1 year
									);
								}

								//print_r('inserting');
								if($message=Yii::app()->mongo->insert('fileToObj',$file))
								{
									//print_r($message);
									if($expireAfter===null){
										$objectStorage->with($folder.'/'.$fileName)
											->setBody($object->getBody())
											->setHeader('Content-type', 'application/octet-stream')
											->create();
									}else{
										$objectStorage->with($folder.'/'.$fileName)
											->setBody($object->getBody())
											->setHeader('Content-type', 'application/octet-stream')
											->deleteAt($expireAfter)
											->create();
									}

								}else{
									return false;
								}

							}else{
								return false;
							}
						} catch (Exception $e) {
							return false;
						}
					}else {
						return false;
					}
				}else {
					return false;
				}

				unset($file);
			}
			return $size;
		}else {
			return $size;
		}
	}

	/**
	 * File Copy when send Pin email
	 * @param $fileObject
	 * @param $userId
	 * @param null $expireAfter
	 * @return bool
	 */
	public function makeCopiesWithModKey($fileObject,$userId,$expireAfter=null)
	{
		//pin email
		//print_r($fileObject);
		$size=0;

		if(is_array($fileObject)){

			$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
			$host = Yii::app()->params['host'];
			$folder=Yii::app()->params['folder'];
			$username = Yii::app()->params['username'];
			$password = Yii::app()->params['password'];


			$objectStorage = new ObjectStorage($host, $username, $password, $options);

			foreach($fileObject as $fileName=>$modKey){

				$mongof=new MongoId(substr($fileName, 0,24));

				if($ref=Yii::app()->mongo->findByManyIds('fileToObj',array($mongof),array('_id'=>1,'modKey'=>1))) {

					if ($ref[0]['modKey'] === hash('sha256', $modKey.$userId)) {

						$fileId=hash('sha256',substr($fileName, 0,24).$userId);

						try{
							$object = $objectStorage->with($folder.'/'.$fileId)->get();

							if($object!='not found'){
								$size+=strlen($object->getBody());

								if($expireAfter===null){
									$objectStorage->with($folder.'/'.$fileName)
										->setBody($object->getBody())
										->setHeader('Content-type', 'application/octet-stream')
										->create();
								}else{
									$objectStorage->with($folder.'/'.$fileName)
										->setBody($object->getBody())
										->setHeader('Content-type', 'application/octet-stream')
										->deleteAt($expireAfter)
										->create();
								}

							}else{
								return false;
							}
						} catch (Exception $e) {
							return false;
						}
					}else {
						return false;
					}
				}else {
					return false;
				}

			}
			return $size;
		}else {
			return $size;
		}
	}

	public function makeCopiesWithMeta($fileObject,$userId,$expireAfter=null)
	{
	//pgp email
		if(is_array($fileObject)){


			$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
			$host = Yii::app()->params['host'];
			$folder=Yii::app()->params['folder'];
			$username = Yii::app()->params['username'];
			$password = Yii::app()->params['password'];

			$objectStorage = new ObjectStorage($host, $username, $password, $options);

			foreach($fileObject as $index=>$fileData){

				$file[]=array(
					//"_id"=>new MongoId($fileData['fileName']),
					"pgpFileName"=>$fileData['fileName'],
					//"type"=>2, // pgp file for recipient
					"base64"=>$fileData['base64'],
					"name"=>$fileData['name'],
					"public"=>true,
					"size"=>$fileData['size'],
					"type"=>$fileData['type'],
					"expireAfter" => new MongoDate($expireAfter),
					"modKey"=>hash('sha256',$fileData['modKey'])
				);

				if($message=Yii::app()->mongo->insert('fileToObj',$file)) {
					$fileId = $message[0];
					unset($file);

					$fName=hash('sha256',substr($fileData['fileName'], 0,24).$userId);
					try{
						$object = $objectStorage->with($folder.'/'.$fName)->get();

						if($expireAfter===null){
							$objectStorage->with($folder.'/'.$fileId)
								->setBody($object->getBody())
								->setHeader('Content-type', 'application/octet-stream')
								->create();
						}else{
							$objectStorage->with($folder.'/'.$fileId)
								->setBody($object->getBody())
								->setHeader('Content-type', 'application/octet-stream')
								//->deleteAfter(20)
								->deleteAt($expireAfter)
								->create();
						}

					} catch (Exception $e) {
						//reverse if fail in the process
						Yii::app()->mongo->removeById('fileToObj',$fileId);
						return false;

					}


				}else{
					return false;
				}

			}
			return true;

		}else {
			return true;
		}
	}


	public function removeFileFromDraft($userId)
	{
		if(strlen($this->fileName)==24){
			$result['response']='fail';

			$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
			$host = Yii::app()->params['host'];
			$folder=Yii::app()->params['folder'];
			$username = Yii::app()->params['username'];
			$password = Yii::app()->params['password'];

			$fOname=$this->fileName;
			$objectStorage = new ObjectStorage($host, $username, $password, $options);

			$mongof[]=new MongoId($fOname);

			if($ref=Yii::app()->mongo->findByManyIds('fileToObj',$mongof,array('_id'=>1,'modKey'=>1,'pgpFileName'=>1))){

				if($ref[0]['modKey']===hash('sha256',$this->modKey.$userId)){
					if($res = $objectStorage->with($folder.'/'.$ref[0]['pgpFileName'])->delete()){
						if($res==1 || $res=='not found'){
							if(Yii::app()->mongo->removeById('fileToObj',$fOname)){
								$result['response']='success';
							}
						}
					}
				}

			}else{
				$result['response']='success';
			}
		}else{
			$result['response']='success';
		}

		echo json_encode($result);

	}

	public function saveNewAttachment($userId)
	{
		$result['response']='fail';

		$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
		$host = Yii::app()->params['host'];
		$username = Yii::app()->params['username'];
		$folder=Yii::app()->params['folder'];
		$password = Yii::app()->params['password'];

		$mngId = new MongoId();
		$fname=(string) $mngId;
		$file[]=array(
			"_id"=>$mngId,
			"pgpFileName"=>hash('sha256',$fname.$userId),
			"emailId"=>hash('sha256',$this->emailId.$this->emailModKey),
			"userId"=>$userId,
			'messageId'=>hash('sha256',$fname.hash('sha256',$fname.$userId)),
			"modKey"=>hash('sha256',$this->modKey.$userId),
			//"expireAfter" => new MongoDate($fileData['expire']),	//DB have expiration for 1 hour
		);

		if($message=Yii::app()->mongo->insert('fileToObj',$file))
		{
			$fileId=hash('sha256',$fname.$userId);

			$objectStorage = new ObjectStorage($host, $username, $password, $options);

			try{
				$objectStorage->with($folder.'/'.$fileId)->setBody($this->file)
					->setHeader('Content-type', 'application/octet-stream')
					->create();

				$result['response']='success';
				$result['fileName']=$message[0];
			} catch (Exception $e) {

			}

		}

		echo json_encode($result);

	}

	/**
	 * File created when received from outside
	 * @param $fileData
	 * @return null
	 */
	public function createNewAttachment($fileData,$messageId)
	{

		$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
		$host = Yii::app()->params['host'];
		$username = Yii::app()->params['username'];
		$folder=Yii::app()->params['folder'];
		$password = Yii::app()->params['password'];

		$mngId = new MongoId();
		$file[]=array(
			"_id"=>$mngId,
			"pgpFileName"=>(string) $mngId,
			"userId"=>0,
			'messageId'=>hash('sha512',$messageId.$fileData['fName']),
			"modKey"=>hash('sha256',$fileData['modKey']),
			"expireAfter" => new MongoDate($fileData['expire']),
		);

		if($message=Yii::app()->mongo->insert('fileToObj',$file))
		{
			$fileId=$message[0];
			$objectStorage = new ObjectStorage($host, $username, $password, $options);

			try{
				$objectStorage->with($folder.'/'.$fileId)->setBody($fileData['data'])
					->setHeader('Content-type', 'application/octet-stream')
					->deleteAt($fileData['expire'])
					->create();
				return $message[0];
			} catch (Exception $e) {
				return null;
			}

		}

	}

	public function getFileSize($fname)
	{

		$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
		$host = Yii::app()->params['host'];
		$folder=Yii::app()->params['folder'];
		$username = Yii::app()->params['username'];
		$password = Yii::app()->params['password'];

		try{
			$fOname=$fname;
			$objectStorage = new ObjectStorage($host, $username, $password, $options);
			$result =$objectStorage->with($folder.'/'.$fOname)->getInfo();
			$size=0;
			if($result!="not found"){
				$size =$result->getHeader('Content-length');
			}

			return $size;

		} catch (Exception $e) {

			if($file=@file_get_contents(Yii::app()->basePath . '/attachments/' .$fname)){
				return strlen($file);

			}
		}
		return "0";
	}


	public function deleteFilesV1($fileArray)
	{
		if(is_array($fileArray)){
			$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
			$host = Yii::app()->params['host'];
			$folder=Yii::app()->params['folder'];
			$username = Yii::app()->params['username'];
			$password = Yii::app()->params['password'];
			$objectStorage = new ObjectStorage($host, $username, $password, $options);
			foreach($fileArray as $fData){

				foreach($fData['name'] as $fName){
					if($fData['v']==15){
						//for transitions V 1.5 files
						$objectStorage->with($folder.'/'.$fName)->delete();
					}else{
						//for old V1 files
						$fOname=hash('sha512',$fName);
						$objectStorage->with($folder.'/'.$fOname)->delete();
					}

				}


			}
		}

	}
	public function deleteFilesV2($fileArray)
	{
		if(is_array($fileArray) && count($fileArray)>1){
			$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
			$host = Yii::app()->params['host'];
			$folder=Yii::app()->params['folder'];
			$username = Yii::app()->params['username'];
			$password = Yii::app()->params['password'];
			$objectStorage = new ObjectStorage($host, $username, $password, $options);

			foreach($fileArray as $fName){
					$objectStorage->with($folder.'/'.$fName)->delete();
			}
		}

	}


	public function encryptFile($data, $key)
	{
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv = openssl_random_pseudo_bytes($iv_size);

		$encryptionMethod = "aes-256-cbc";

			if ($encryptedMessage = base64_encode($iv).';'.openssl_encrypt(base64_encode($data), $encryptionMethod, $key, 0, $iv)) {
				return $encryptedMessage;
			} else
				return false;


	}

}