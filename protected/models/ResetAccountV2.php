<?php

/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */
class ResetAccountV2 extends CFormModel
{

	public $email, $tokenHash, $tokenAesHash;
	public $newPass, $oldPass;

	public
		$userObject,
		$contactObject,
		$blackListObject,
		$updateKeys,
		$profileObject,
		$folderObject,
		$modKey,
		$profileVersion,
		$oldTokenAesHash,
		$salt;


	public function rules()
	{
		return array(

			array('email,tokenHash', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'checkStep', 'message' => 'incorrectData'),
			array('email,tokenHash,tokenAesHash', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'checkTokenHashes', 'message' => 'incorrectData'),

			array('email,tokenHash,tokenAesHash,newPass', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'changeLoginPass', 'message' => 'incorrectData'),

			array('email,tokenAesHash', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'checkToken', 'message' => 'incorrectData'),

			array('email,tokenAesHash,oldPass', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'checkLoginToken', 'message' => 'incorrectData'),


			array('folderObject,updateKeys,profileObject,userObject,contactObject,blackListObject', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'resetUserObject', 'message' => 'fld2upd'),

			array('profileObject,userObject', 'length', 'max' => 3000000, 'min' => 20, 'on' => 'resetUserObject', 'message' => 'fld2upd'),

			array('folderObject,contactObject,blackListObject', 'length', 'max' => 13000000, 'min' => 20, 'on' => 'resetUserObject', 'message' => 'fld2upd'),

			array('profileVersion', 'numerical', 'integerOnly' => true, 'allowEmpty' => false, 'on' => 'resetUserObject', 'message' => 'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32}$/i", 'allowEmpty' => false, 'on' => 'resetUserObject', 'message' => 'fld2upd'),
			array('tokenHash,tokenAesHash,oldTokenAesHash,oldPass', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'resetUserObject', 'message' => 'fld2upd'),

			array('email', 'email', 'allowEmpty' => false, 'on' => 'resetUserObject', 'message' => 'fld2upd'),


			//createNewUser

			array('folderObject,updateKeys,profileObject,userObject,contactObject,blackListObject', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'createNewUser', 'message' => 'fld2upd'),

			array('profileObject,userObject', 'length', 'max' => 3000000, 'min' => 20, 'on' => 'createNewUser', 'message' => 'fld2upd'),

			array('folderObject,contactObject,blackListObject', 'length', 'max' => 13000000, 'min' => 20, 'on' => 'createNewUser', 'message' => 'fld2upd'),

			array('profileVersion', 'numerical', 'integerOnly' => true, 'allowEmpty' => false, 'on' => 'createNewUser', 'message' => 'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32}$/i", 'allowEmpty' => false, 'on' => 'createNewUser', 'message' => 'fld2upd'),
			array('newPass,tokenHash,tokenAesHash', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'createNewUser', 'message' => 'fld2upd'),
			array('salt', 'match', 'pattern' => "/^[a-f0-9\d]{512}$/i", 'allowEmpty' => false, 'on' => 'createNewUser', 'message' => 'fld2upd'),

			array('email', 'length', 'max' => 250, 'min' => 3, 'on' => 'createNewUser', 'message' => 'fld2upd'),
			array('email', 'email', 'allowEmpty' => false, 'on' => 'createNewUser', 'message' => 'fld2upd'),

			//resetUser

			array('folderObject,updateKeys,profileObject,userObject,contactObject,blackListObject', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'resetUser', 'message' => 'fld2upd'),

			array('profileObject,userObject', 'length', 'max' => 3000000, 'min' => 20, 'on' => 'resetUser', 'message' => 'fld2upd'),

			array('folderObject,contactObject,blackListObject', 'length', 'max' => 13000000, 'min' => 20, 'on' => 'resetUser', 'message' => 'fld2upd'),

			array('profileVersion', 'numerical', 'integerOnly' => true, 'allowEmpty' => false, 'on' => 'resetUser', 'message' => 'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32}$/i", 'allowEmpty' => false, 'on' => 'resetUser', 'message' => 'fld2upd'),
			array('newPass,oldTokenAesHash,tokenHash,tokenAesHash', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'resetUser', 'message' => 'fld2upd'),

			array('email', 'length', 'max' => 250, 'min' => 3, 'on' => 'resetUser', 'message' => 'fld2upd'),
			array('email', 'email', 'allowEmpty' => false, 'on' => 'resetUser', 'message' => 'fld2upd'),

			//resetUserTwoStep
			array('folderObject,updateKeys,profileObject,userObject,contactObject,blackListObject', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'resetUserTwoStep', 'message' => 'fld2upd'),

			array('profileObject,userObject', 'length', 'max' => 3000000, 'min' => 20, 'on' => 'resetUserTwoStep', 'message' => 'fld2upd'),

			array('folderObject,contactObject,blackListObject', 'length', 'max' => 13000000, 'min' => 20, 'on' => 'resetUserTwoStep', 'message' => 'fld2upd'),

			array('profileVersion', 'numerical', 'integerOnly' => true, 'allowEmpty' => false, 'on' => 'resetUserTwoStep', 'message' => 'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32}$/i", 'allowEmpty' => false, 'on' => 'resetUserTwoStep', 'message' => 'fld2upd'),
			array('oldTokenAesHash,tokenHash,tokenAesHash,oldPass', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'resetUserTwoStep', 'message' => 'fld2upd'),

			array('email', 'length', 'max' => 250, 'min' => 3, 'on' => 'resetUserTwoStep', 'message' => 'fld2upd'),
			array('email', 'email', 'allowEmpty' => false, 'on' => 'resetUserTwoStep', 'message' => 'fld2upd')

			/*
						array('folderObject,updateKeys,profileObject,userObject,contactObject,blackListObject', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'updateV2','message'=>'fld2upd'),

						array('profileObject,userObject','length', 'max'=>3000000,'min'=>20,'on'=>'updateV2','message'=>'fld2upd'),

						array('folderObject,contactObject,blackListObject','length', 'max'=>13000000,'min'=>20,'on'=>'updateV2','message'=>'fld2upd'),

						array('profileVersion', 'numerical','integerOnly'=>true,'allowEmpty'=>false, 'on' => 'updateV2','message'=>'fld2upd'),

						array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'updateV2','message'=>'fld2upd'),
						array('tokenHash,tokenAesHash', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'updateV2','message'=>'fld2upd'),



						array('folderObject,profileObject,userObject,contactObject,blackListObject,plan', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'updateV2','message'=>'fld2save'),
			*/

		);
	}

	public function checkLoginToken()
	{
		$result['response'] = 'fail';
		$criteria = array('mailHash' => $this->email, 'tokenAesHash' => $this->tokenAesHash);
		if ($user = Yii::app()->mongo->findOne('user', $criteria, array('password' => 1))) {
			if ($user['password'] == crypt($this->oldPass, $user['password'])) {
				$result['response'] = 'success';

			} else {
				$result['response'] = 'wrongPass';
			}

		} else {
			$result['response'] = 'notFound';
		}
		echo json_encode($result);
	}

	public function changeLoginPass()
	{
		$result['response'] = 'fail';
		$criteria = array('mailHash' => $this->email, 'tokenHash' => $this->tokenHash, 'tokenAesHash' => $this->tokenAesHash);

		if ($user = Yii::app()->mongo->findOne('user', $criteria, array('password' => 1))) {
			$Crawler = new CrawlerV2();
			$salt = base64_encode(($Crawler->makeModKey(10)));

			$userNewPass = array(
				"password" => crypt($this->newPass, '$6$' . $salt . '$')
			);

			if ($prof = Yii::app()->mongo->update('user', $userNewPass, $criteria)) {
				$result['response'] = 'success';
			}

		} else {
			$result['response'] = 'incorrect';
		}

		echo json_encode($result);
	}

	public function checkToken()
	{

		$result['response'] = 'fail';

		$criteria = array('mailHash' => $this->email, 'tokenAesHash' => $this->tokenAesHash);

		if ($user = Yii::app()->mongo->findOne('user', $criteria, array('oneStep' => 1, 'saltS' => 1))) {
			$result['response'] = 'success';
			$result['oneStep'] = $user['oneStep'];
			$result['saltS'] = $user['saltS'];
		} else {
			$result['response'] = 'notFound';
		}

		echo json_encode($result);

	}

	public function checkTokenHashes()
	{

		$result['response'] = 'fail';

		$criteria = array('mailHash' => $this->email, 'tokenHash' => $this->tokenHash, 'tokenAesHash' => $this->tokenAesHash);

		if ($user = Yii::app()->mongo->findOne('user', $criteria, array('oneStep' => 1, 'saltS' => 1))) {
			$result['response'] = 'success';

		} else {
			$result['response'] = 'incorrect';
		}

		echo json_encode($result);

	}

	public function checkStep()
	{

		$result['response'] = 'fail';
		$criteria = array('mailHash' => $this->email, 'tokenAesHash' => $this->tokenHash);

		if ($user = Yii::app()->mongo->findOne('user', $criteria, array('oneStep' => 1, 'saltS' => 1))) {
			$result['response'] = 'success';
			$result['oneStep'] = $user['oneStep'];
			$result['saltS'] = $user['saltS'];

		} else {
			$result['response'] = 'notFound';
		}
		echo json_encode($result);

	}


	public function createNewUser()
	{

		$result['response'] = 'fail';

		//$result['response'] = 'success';
		//echo json_encode($result);

		//Yii::app()->end();

		$newPlan = Yii::app()->params['params']['planData'][1];

		$Crawler = new CrawlerV2();
		$salt = base64_encode(($Crawler->makeModKey(10)));
		$userNew[] = array(

			"mailHash" => hash('sha512', $this->email),
			"password" => crypt($this->newPass, '$6$' . $salt . '$'),
			"modKey" => hash('sha512', $this->modKey),

			"created" => new MongoDate(strtotime('now')),
			"saltS" => $this->salt,
			"tokenHash" => $this->tokenHash,
			"tokenAesHash" => $this->tokenAesHash,
			"active" => new MongoDate(strtotime('now')),
			"oneStep" => 1,
			"version" => 2,
			"authSecret" => null,
			"2ndType" => null,

			'cycleStart' => new MongoDate(strtotime('now')),
			'cycleEnd' => new MongoDate(strtotime('now' . '+ 1 week')),
			'balance' => 0,
			'alrdPaid' => 0,
			'pastDue' => 0,
			'monthlyCharge' => Yii::app()->params['params']['planData'][1]['price'],
			'creditUsed' => false,
			'planData' => json_encode($newPlan),
            'backVersion'=>3,
            'paymentVersion'=>2,
            'planSelected'=>1,
            'userTrial'=>true
		);


		$key = json_decode($this->updateKeys, true);

		//$SavingUserDataV2=new SavingUserDataV2();
		//$checkKey=$SavingUserDataV2->checkPGP(base64_decode(array_values($key)[0]));


		//check if email in addresses
		$updateKeys = json_decode($this->updateKeys, true);

		$CheckIfExistV2 = new CheckIfExistV2();
		$CheckIfExistV2->email = $this->email;

		$dom = explode('@', strtolower($this->email))[1];


		if ($CheckIfExistV2->validateEmail('', 'df') === 'true' && $dom === Yii::app()->params['params']['registeredDomain']) {
			//if($CheckIfExistV2->validateEmail('','df')==='true' && $dom==='scryptmail.com' && $checkKey){


			if ($message = Yii::app()->mongo->insert('user', $userNew)) {

				$newUSerId = $message[0];

				$userObj[] = array(
					"userId" => $newUSerId,
					"userObj" => new MongoBinData($this->userObject, MongoBinData::GENERIC),
					"profileSettings" => new MongoBinData($this->profileObject, MongoBinData::GENERIC),
					//"folderObj" => new MongoBinData($this->folderObject, MongoBinData::GENERIC),
					"contacts" => new MongoBinData($this->contactObject, MongoBinData::GENERIC),
					"blackList" => new MongoBinData($this->blackListObject, MongoBinData::GENERIC),
					"modKey" => hash('sha512', $this->modKey)
				);

				if ($userObjSaved = Yii::app()->mongo->insert('userObjects', $userObj)) {

                    $folderDec = json_decode($this->folderObject, true);
                    $newFolderDoc=array();

                    foreach($folderDec as $k=>$data){
                        $newFolderDoc[$k]=$data;
                        $newFolderDoc[$k]['userId']=$newUSerId;
                        $newFolderDoc[$k]['index']=(int)$newFolderDoc[$k]['index'];
                    }


                    Yii::app()->mongo->insert('folderObj', $newFolderDoc);


					$addresses[] = array(
						'addressHash' => hash('sha512', $this->email),
						'mailKey' => array_values($key)[0],
						'userId' => $newUSerId,
						'addr_type' => 1,
						'vdId' => 2,
						'v' => 2,
						'active' => 1
					);

				}
				if ($userPlanSaved = Yii::app()->mongo->insert('addresses', $addresses)) {
					$stats = new StatsV2;
					$stats->counter('newUser');

					$result['response'] = 'success';
				}

			}

		}


		echo json_encode($result);

	}


	//reset user when he used only single password
	public function resetUser()
	{
		$result['response'] = 'fail';


		$criteria = array('mailHash' => hash('sha512', $this->email), 'tokenAesHash' => $this->oldTokenAesHash);
		if ($user = Yii::app()->mongo->findOne('user', $criteria)) {

			$addressObj = array(
				"active" => 0,
				"retentionStarted" => new MongoDate(strtotime('now')),
			);

			$userId=$user['_id'];
			$criteria = array("userId" => $user['_id'], "addr_type" => array('$ne' => 1), "retentionStarted" => array('$not' => array('$exists' => true)));

			//disabling usera ddresses
				Yii::app()->mongo->update('addresses', $addressObj, $criteria);
			//todo add custom domain into new account immediately


				$param[':userId'] = $user['_id'];
				Yii::app()->db->createCommand('DELETE FROM virtual_domains where userId=:userId AND globalDomain=0')->execute($param);
				//remove after create virt domain direct call


				//delete old files
				$criteria = array('userId' => $user['_id']);

				if ($ref = Yii::app()->mongo->findAll('fileToObj', $criteria, array('pgpFileName' => 1))) {

					$files2Rem = array();
					foreach ($ref as $row) {
						$files2Rem[] = $row['pgpFileName'];
					}
					FileWorkerV2::deleteFilesV2($files2Rem);

					$mngDataAttachments = array('userId' => $user['_id']);

					Yii::app()->mongo->removeAll('fileToObj', $mngDataAttachments);
				}

				//delete emails V2
				$persFold = array('userId' => $user['_id'], 'v' => 2);
				Yii::app()->mongo->removeAll('personalFolders', $persFold);

				//delete email V1
				if(isset($user['oldId'])){
					$criteria = array('userId' => $user['oldId']);

					if ($ref = Yii::app()->mongo->findAll('personalFolders', $criteria, array('file' => 1, 'v' => 1))) {
						$files2Rem = array();

						foreach ($ref as $emData) {
							if (!empty($emData['file']) && $emData['file'] !== null && $emData['file'] != "null") {

								$file['name'] = json_decode($emData['file']);

								$file['v'] = isset($emData['v']) ? $emData['v'] : 1; //old emails without version
								$files2Rem[] = $file;
								unset($file);
							}
						}

						$criteria = array('userId' => $user['oldId']);
						Yii::app()->mongo->removeAll('personalFolders', $criteria);

						FileWorkerV2::deleteFilesV1($files2Rem);


					}
				}


				//delete user objects
				//$criteria = array('userId' => $user['_id']);
				//Yii::app()->mongo->removeAll('userObjects', $criteria);




			//proceed with setting new data for user

			$Crawler = new CrawlerV2();
			$salt = base64_encode(($Crawler->makeModKey(10)));
			$userNew = array(

				"mailHash" => hash('sha512', $this->email),
				"password" => crypt($this->newPass, '$6$' . $salt . '$'),
				"modKey" => hash('sha512', $this->modKey),
                "authSecret"=>null,
                "2ndType"=>null,
				"tokenHash" => $this->tokenHash,
				"tokenAesHash" => $this->tokenAesHash,
				"active" => new MongoDate(strtotime('now'))
			);


			$key = json_decode($this->updateKeys, true);


			//check if email in addresses
			$updateKeys = json_decode($this->updateKeys, true);

			$CheckIfExistV2 = new CheckIfExistV2();
			$CheckIfExistV2->email = $this->email;

			$dom = explode('@', strtolower($this->email))[1];

			//todo verify if exist not the opposite

			if ($CheckIfExistV2->validateEmail('', 'df') === 'false' && $dom === Yii::app()->params['params']['registeredDomain']) {

				//if($CheckIfExistV2->validateEmail('','df')==='true' && $dom==='scryptmail.com' && $checkKey){

				$criteria=array("_id" => new MongoId($userId));
				if ($message = Yii::app()->mongo->update('user', $userNew,$criteria)) {

					$userObj = array(
						"userObj" => new MongoBinData($this->userObject, MongoBinData::GENERIC),
						"profileSettings" => new MongoBinData($this->profileObject, MongoBinData::GENERIC),
					//	"folderObj" => new MongoBinData($this->folderObject, MongoBinData::GENERIC),
						"contacts" => new MongoBinData($this->contactObject, MongoBinData::GENERIC),
						"blackList" => new MongoBinData($this->blackListObject, MongoBinData::GENERIC),
						"modKey" => hash('sha512', $this->modKey)
					);

					$criteria=array("userId" => $userId);
                    $unset=array("folderObj"=>1);
                    Yii::app()->mongo->unsetField('userObjects',$unset,$criteria);

					if ($userObjSaved = Yii::app()->mongo->update('userObjects', $userObj,$criteria,null,true)) {

                        $criteria=array("userId" =>$userId);
                        Yii::app()->mongo->removeAll('folderObj',$criteria);

                        $folderDec = json_decode($this->folderObject, true);
                        $newFolderDoc=array();

                        foreach($folderDec as $k=>$data){
                            $newFolderDoc[$k]=$data;
                            $newFolderDoc[$k]['userId']=$userId;
                            $newFolderDoc[$k]['index']=(int)$newFolderDoc[$k]['index'];
                        }

                        Yii::app()->mongo->insert('folderObj', $newFolderDoc);

						$addresses = array(
							'addressHash' => hash('sha512', $this->email),
							'mailKey' => array_values($key)[0],
							'addr_type' => 1,
							'vdId' => 2,
							'v' => 2,
							'active' => 1
						);

						$criteria=array("userId" => $userId,'addr_type'=>1);
						if ($userPlanSaved = Yii::app()->mongo->update('addresses', $addresses,$criteria)) {
							$stats = new StatsV2;
							$stats->counter('newUser');

							$result['response'] = 'success';
						}


					}

				}

			}

		}
		echo json_encode($result);
	}


	//reset objects when user have 2 password authentication
	public function resetUserTwoStep()
	{
		$result['response'] = 'fail';


		$criteria = array('mailHash' => hash('sha512', $this->email), 'tokenAesHash' => $this->oldTokenAesHash);
		if ($user = Yii::app()->mongo->findOne('user', $criteria)) {
			if ($user['password'] == crypt($this->oldPass, $user['password'])) {

				$addressObj = array(
					"active" => 0,
					"retentionStarted" => new MongoDate(strtotime('now')),
				);

				$userId=$user['_id'];
				$criteria = array("userId" => $user['_id'], "addr_type" => array('$ne' => 1), "retentionStarted" => array('$not' => array('$exists' => true)));

				//disabling usera ddresses
				Yii::app()->mongo->update('addresses', $addressObj, $criteria);
				//todo add custom domain into new account immediately


				$param[':userId'] = $user['_id'];
				Yii::app()->db->createCommand('DELETE FROM virtual_domains where userId=:userId AND globalDomain=0')->execute($param);
				//remove after create virt domain direct call


				//delete old files
				$criteria = array('userId' => $user['_id']);

				if ($ref = Yii::app()->mongo->findAll('fileToObj', $criteria, array('pgpFileName' => 1))) {

					$files2Rem = array();
					foreach ($ref as $row) {
						$files2Rem[] = $row['pgpFileName'];
					}
					FileWorkerV2::deleteFilesV2($files2Rem);

					$mngDataAttachments = array('userId' => $user['_id']);

					Yii::app()->mongo->removeAll('fileToObj', $mngDataAttachments);
				}

				//delete emails V2
				$persFold = array('userId' => $user['_id'], 'v' => 2);
				Yii::app()->mongo->removeAll('personalFolders', $persFold);

				//delete email V1
                if(isset($user['oldId'])){
                    $criteria = array('userId' => $user['oldId']);

                    if ($ref = Yii::app()->mongo->findAll('personalFolders', $criteria, array('file' => 1, 'v' => 1))) {
                        $files2Rem = array();

                        foreach ($ref as $emData) {
                            if (!empty($emData['file']) && $emData['file'] !== null && $emData['file'] != "null") {

                                $file['name'] = json_decode($emData['file']);

                                $file['v'] = isset($emData['v']) ? $emData['v'] : 1; //old emails without version
                                $files2Rem[] = $file;
                                unset($file);
                            }
                        }

                        $criteria = array('userId' => $user['oldId']);
                        Yii::app()->mongo->removeAll('personalFolders', $criteria);

                        FileWorkerV2::deleteFilesV1($files2Rem);

                    }
                }


				//delete user objects
				//$criteria = array('userId' => $user['_id']);
				//Yii::app()->mongo->removeAll('userObjects', $criteria);




				//proceed with setting new data for user

				$Crawler = new CrawlerV2();
				$salt = base64_encode(($Crawler->makeModKey(10)));
				$userNew = array(

					"mailHash" => hash('sha512', $this->email),
					"modKey" => hash('sha512', $this->modKey),
                    "authSecret"=>null,
                    "2ndType"=>null,
					"tokenHash" => $this->tokenHash,
					"tokenAesHash" => $this->tokenAesHash,
					"active" => new MongoDate(strtotime('now'))
				);


				$key = json_decode($this->updateKeys, true);


				//check if email in addresses
				$updateKeys = json_decode($this->updateKeys, true);

				$CheckIfExistV2 = new CheckIfExistV2();
				$CheckIfExistV2->email = $this->email;

				$dom = explode('@', strtolower($this->email))[1];

				//todo verify if exist not the opposite

				if ($CheckIfExistV2->validateEmail('', 'df') === 'false' && $dom === Yii::app()->params['params']['registeredDomain']) {

					//if($CheckIfExistV2->validateEmail('','df')==='true' && $dom==='scryptmail.com' && $checkKey){

					$criteria=array("_id" => new MongoId($userId));
					if ($message = Yii::app()->mongo->update('user', $userNew,$criteria)) {

						$userObj = array(
							"userObj" => new MongoBinData($this->userObject, MongoBinData::GENERIC),
							"profileSettings" => new MongoBinData($this->profileObject, MongoBinData::GENERIC),
							//"folderObj" => new MongoBinData($this->folderObject, MongoBinData::GENERIC),
							"contacts" => new MongoBinData($this->contactObject, MongoBinData::GENERIC),
							"blackList" => new MongoBinData($this->blackListObject, MongoBinData::GENERIC),
							"modKey" => hash('sha512', $this->modKey)
						);

						$criteria=array("userId" => $userId);

                        $unset=array("folderObj"=>1);
                        Yii::app()->mongo->unsetField('userObjects',$unset,$criteria);

						if ($userObjSaved = Yii::app()->mongo->update('userObjects', $userObj,$criteria,null,true)) {

                            $criteria=array("userId" =>$userId);
                            Yii::app()->mongo->removeAll('folderObj',$criteria);

                            $folderDec = json_decode($this->folderObject, true);
                            $newFolderDoc=array();

                            foreach($folderDec as $k=>$data){
                                $newFolderDoc[$k]=$data;
                                $newFolderDoc[$k]['userId']=$userId;
                                $newFolderDoc[$k]['index']=(int)$newFolderDoc[$k]['index'];
                            }

                            Yii::app()->mongo->insert('folderObj', $newFolderDoc);

							$addresses = array(
								'addressHash' => hash('sha512', $this->email),
								'mailKey' => array_values($key)[0],
								'addr_type' => 1,
								'vdId' => 2,
								'v' => 2,
								'active' => 1
							);

							$criteria=array("userId" => $userId,'addr_type'=>1);
							if ($userPlanSaved = Yii::app()->mongo->update('addresses', $addresses,$criteria)) {
								$stats = new StatsV2;
								$stats->counter('newUser');

								$result['response'] = 'success';
							}


						}

					}

				}


			}else{
				$result['response'] = 'wrngPass';
			}


		}
		echo json_encode($result);
	}


	public function resetUserObject()
	{


		//print_r($this->userObject);
		//print_r($this->contactObject);
		//print_r($this->blackListObject);
		//print_r($this->updateKeys);
		//print_r($this->profileObject);
		//	print_r($this->folderObject);
		//	print_r($this->modKey);
		//	print_r($this->profileVersion);
		//	print_r($this->tokenHash);
		//	print_r($this->tokenAesHash);

		/*



		*/
		$criteria = array('mailHash' => hash('sha512', $this->email), 'tokenAesHash' => $this->oldTokenAesHash);

		if ($user = Yii::app()->mongo->findOne('user', $criteria)) {
			//user found and his token is correct, verify his password
			if ($user['password'] == crypt($this->oldPass, $user['password'])) {
				//now we trust user to do dirty work
				//1 delete all emails and files

				$fileArrayV1 = array();
				$fileArrayV2 = array();
				if (isset($user['oldId'])) {
					$data[] = array('userId' => $user['oldId']);
				}
				$data[] = array('userId' => $user['_id']);

				$criteria = array('$or' => $data);

				if ($ref = Yii::app()->mongo->findAll('personalFolders', $criteria, array('_id' => 1, 'modKey' => 1, 'userId' => 1, 'file' => 1))) {

					foreach ($ref as $emData) {
						if (isset($emData['file'])) {

							$file['name'] = json_decode($emData['file']);

							$file['v'] = isset($emData['v']) ? $emData['v'] : 1;

							if ($file['v'] == 1) {
								$fileArrayV1[] = $file;
							} else {
								$fileArrayV2[] = $file;
							}

							unset($file);
						}

					}
					//print_r($fileArrayV2);
				}

				//todo send to file delete
				//$fileArrayV1
				//$fileArrayV2


				//print_r($user);

			} else {
				$result['response'] = 'wrongPass';
			}

		} else {
			$result['response'] = 'notFound';
		}

		echo json_encode($result);
	}


}
