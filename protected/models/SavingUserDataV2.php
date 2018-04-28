<?php
/**
 * Author: Sergei Krutov
 * Date: 9/1/15
 * For: SCRYPTmail.com.
 * Version: RC 0.99
 */

class SavingUserDataV2 extends CFormModel
{

	public $userToken;

	public $objectName,$objectData,$modKey;

	public $email,$publicKey,$type;
	public $domain,$vrfString;

	public $filterData,$folderData;

	public $hashKey;

	public $emailData;

	public $sender,$subject,$totalRecipients;

	public $seedEmails;

	public $emailToDelete;

	//for update to Version2
	/*
	public $profileObject,
		$folderObject,
		$modKey,
		$profileVersion,
		$userObject,
		$contactObject,
		$blackListObject,
		$plan;
*/
	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),
			array('objectName,objectData,modKey', 'safe'),

			array('objectData', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'updatingObjects','message'=>'fld2upd'),
			array('objectData','length', 'max'=>3000000,'min'=>20,'on'=>'updatingObjects','message'=>'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'updatingObjects,savingUserObjWnewPGP,savingUserObjWdeletePGP','message'=>'fld2upd'),

			array('publicKey', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'savingUserObjWnewPGP','message'=>'fld2upd'),
			array('publicKey','length', 'max'=>30000,'min'=>20,'on'=>'savingUserObjWnewPGP','message'=>'fld2upd'),

			array('type', 'numerical', 'integerOnly' => true, 'on' => 'savingUserObjWnewPGP'),
			array('email', 'email', 'allowEmpty' => false, 'on' => 'savingUserObjWnewPGP'),

			array('email', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'savingUserObjWdeletePGP','message'=>'fld2upd'),


			//savePendingDomainV2
			array('vrfString', 'match', 'pattern' => "/^[a-z0-9\d]{64}$/i", 'allowEmpty' => false, 'on' => 'savePendingDomain','message'=>'chckVrf'),
			array('domain', 'url', 'defaultScheme' => 'http', 'on' => 'savePendingDomain','message'=>'addPending'),

			array('objectData', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'savePendingDomain','message'=>'fld2upd'),
			array('objectData','length', 'max'=>3000000,'min'=>20,'on'=>'savePendingDomain','message'=>'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'savePendingDomain','message'=>'fld2upd'),

			//updateDomain
			array('vrfString', 'match', 'pattern' => "/^[a-z0-9\d]{64}$/i", 'allowEmpty' => false, 'on' => 'updateDomain','message'=>'chckVrf'),
			array('domain', 'url', 'defaultScheme' => 'http', 'on' => 'updateDomain','message'=>'addPending'),

			array('objectData', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'updateDomain','message'=>'fld2upd'),
			array('objectData','length', 'max'=>3000000,'min'=>20,'on'=>'updateDomain','message'=>'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'updateDomain','message'=>'fld2upd'),


			//deleteDomain
			array('domain', 'url', 'defaultScheme' => 'http', 'on' => 'deleteDomain','message'=>'addPending'),

			array('objectData', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'deleteDomain','message'=>'fld2upd'),
			array('objectData','length', 'max'=>3000000,'min'=>20,'on'=>'deleteDomain','message'=>'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'deleteDomain','message'=>'fld2upd'),


			//folderSettings
			array('folderData', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'folderSettings','message'=>'fld2upd'),
			array('folderData','length', 'max'=>8000000,'min'=>20,'on'=>'folderSettings','message'=>'fld2upd'),


			array('filterData', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'folderSettings','message'=>'fld2upd'),
			array('filterData','length', 'max'=>3000000,'min'=>20,'on'=>'folderSettings','message'=>'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'folderSettings','message'=>'fld2upd'),



			//savingUserObjWnewPGPkeys

			array('publicKey', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'savingUserObjWnewPGPkeys','message'=>'fld2upd'),
			array('publicKey','length', 'max'=>30000,'min'=>20,'on'=>'savingUserObjWnewPGPkeys','message'=>'fld2upd'),


			array('email', 'email', 'allowEmpty' => false, 'on' => 'savingUserObjWnewPGPkeys'),


			array('objectData', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'savingUserObjWnewPGPkeys','message'=>'fld2upd'),
			array('objectData','length', 'max'=>3000000,'min'=>20,'on'=>'savingUserObjWnewPGPkeys','message'=>'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'savingUserObjWnewPGPkeys','message'=>'fld2upd'),


			//saveDraftEmail
			array('folderData,emailData', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'saveDraftEmail','message'=>'fld2upd'),
			array('folderData,emailData','length', 'max'=>8000000,'allowEmpty' => true,'on'=>'saveDraftEmail','message'=>'fld2upd'),



			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'saveDraftEmail','message'=>'fld2upd'),


			//sendEmailClearText
			array('folderData', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'sendEmailClearText','message'=>'fld2upd'),
			array('folderData','length', 'max'=>8000000,'allowEmpty' => true,'on'=>'sendEmailClearText','message'=>'fld2upd'),

			array('emailData', 'match', 'pattern' => "/^[a-zA-Z0-9+{\[\]:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'sendEmailClearText','message'=>'fld2upd'),

			array('emailData','length', 'max'=>600000,'allowEmpty' => false,'on'=>'sendEmailClearText','message'=>'fld2upd'),


			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'sendEmailClearText','message'=>'fld2upd'),

			array('emailData', 'checkDatas', 'on' => 'sendEmailClearText'),

			//sendEmailWithPin


			array('folderData', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'sendEmailWithPin','message'=>'fld2upd'),
			array('folderData','length', 'max'=>8000000,'allowEmpty' => true,'on'=>'sendEmailWithPin','message'=>'fld2upd'),

			array('emailData', 'match', 'pattern' => "/^[a-zA-Z0-9+{\[\]:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'sendEmailWithPin','message'=>'fld2upd'),
			array('emailData','length', 'max'=>6000000,'allowEmpty' => false,'on'=>'sendEmailWithPin','message'=>'fld2upd'),

			array('subject,sender', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'sendEmailWithPin','message'=>'fld2upd'),
			array('subject','length', 'max'=>300,'allowEmpty' => true,'on'=>'sendEmailWithPin','message'=>'fld2upd'),

			array('sender','length', 'max'=>300,'allowEmpty' => false,'on'=>'sendEmailWithPin','message'=>'fld2upd'),


			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'sendEmailWithPin','message'=>'fld2upd'),

			array('emailData', 'checkDatasPin', 'on' => 'sendEmailWithPin'),

			//sendEmailPGP

			array('folderData', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'sendEmailPGP','message'=>'fld2upd'),
			array('folderData','length', 'max'=>8000000,'allowEmpty' => true,'on'=>'sendEmailPGP','message'=>'fld2upd'),

			array('emailData', 'match', 'pattern' => "/^[a-zA-Z0-9+{\[\]:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'sendEmailPGP','message'=>'fld2upd'),
			array('emailData','length', 'max'=>600000,'allowEmpty' => false,'on'=>'sendEmailPGP','message'=>'fld2upd'),

			array('subject,sender', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'sendEmailPGP','message'=>'fld2upd'),
			array('subject','length', 'max'=>300,'allowEmpty' => true,'on'=>'sendEmailPGP','message'=>'fld2upd'),

			array('sender','length', 'max'=>300,'allowEmpty' => false,'on'=>'sendEmailPGP','message'=>'fld2upd'),


			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'sendEmailPGP','message'=>'fld2upd'),

			array('emailData', 'checkDatasPGP', 'on' => 'sendEmailPGP'),

			//sendEmailIntV2

			array('folderData', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'sendEmailInt','message'=>'fld2upd'),
			array('folderData','length', 'max'=>8000000,'allowEmpty' => true,'on'=>'sendEmailInt','message'=>'fld2upd'),

			array('emailData', 'match', 'pattern' => "/^[a-zA-Z0-9+{\[\]:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'sendEmailInt','message'=>'fld2upd'),
			array('emailData','length', 'max'=>600000,'allowEmpty' => false,'on'=>'sendEmailInt','message'=>'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'sendEmailInt','message'=>'fld2upd'),

			array('emailData', 'checkDatasInt', 'on' => 'sendEmailInt'),


			//saveNewEmailOld
			array('seedEmails', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\[\]\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'saveNewEmailOld','message'=>'fld2upd'),
			array('seedEmails','length', 'max'=>300000,'allowEmpty' => true,'on'=>'saveNewEmailOld','message'=>'fld2upd'),
			array('seedEmails', 'checkSeedEmails', 'on' => 'saveNewEmailOld'),

			array('folderData', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'saveNewEmailOld','message'=>'fld2upd'),
			array('folderData','length', 'max'=>8000000,'allowEmpty' => true,'on'=>'saveNewEmailOld','message'=>'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'saveNewEmailOld','message'=>'fld2upd'),

			//saveNewEmailV2

			array('seedEmails', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\[\]\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'saveNewEmailV2','message'=>'fld2upd'),
			array('seedEmails','length', 'max'=>300000,'allowEmpty' => true,'on'=>'saveNewEmailV2','message'=>'fld2upd'),
			array('seedEmails', 'checkEmailsv2', 'on' => 'saveNewEmailV2'),

			array('folderData', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'saveNewEmailV2','message'=>'fld2upd'),
			array('folderData','length', 'max'=>14000000,'allowEmpty' => true,'on'=>'saveNewEmailV2','message'=>'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'saveNewEmailV2','message'=>'fld2upd'),


			//deleteEmailV2
			array('folderData', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'deleteEmailV2','message'=>'fld2upd'),
			array('folderData','length', 'max'=>19000000,'allowEmpty' => true,'on'=>'deleteEmailV2','message'=>'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'deleteEmailV2','message'=>'fld2upd'),

			array('emailToDelete', 'checkEmailToDelete', 'on' => 'deleteEmailV2'),

			/*
			 * updatingObjects
			 *
			array('folderObject,profileObject,userObject,contactObject,blackListObject', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'updateV2','message'=>'fld2upd'),

			array('profileObject,userObject','length', 'max'=>3000000,'min'=>20,'on'=>'updateV2','message'=>'fld2upd'),

			array('folderObject,contactObject,blackListObject','length', 'max'=>13000000,'min'=>20,'on'=>'updateV2','message'=>'fld2upd'),

			array('profileVersion', 'numerical','integerOnly'=>true,'allowEmpty'=>false, 'on' => 'updateV2','message'=>'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'updateV2','message'=>'fld2upd'),


			array('folderObject,profileObject,userObject,contactObject,blackListObject,plan', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'updateAllUsersObj','message'=>'fld2save'),

*/
		);
	}



	public function extract_email_address ($string) {

		if (strpos($string,'<') !== false) {
			$broken=explode('<',$string);

			$emails=array();
			foreach(preg_split('/\s/', $broken[1]) as $token) {

                if(strlen($token)<80){
                    $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
                }else if(strlen($token)<320){
                    $email = filter_var($token, FILTER_SANITIZE_EMAIL);
                }


				if ($email !== false && strlen($email)<320) {
					$emails[] = strtolower($email);
				}

			}


        }else{
			$emails=array();
			foreach(preg_split('/\s/', $string) as $token) {

                if(strlen($token)<80){
                    $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
                }else if(strlen($token)<320){
                    $email = filter_var($token, FILTER_SANITIZE_EMAIL);
                }

                if ($email !== false && strlen($email)<320) {
					$emails[] = strtolower($email);
				}
			}
		}

		return $emails;
	}

	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}

	public function updatingFilterObj($filterObj,$modKey,$userId)
	{


		if($object=Yii::app()->mongo->findByUserIdNew('userObjects', $userId, array('blackList' => 1))){


			$submitedObject=json_decode($filterObj,true);
			$objectDecoded=json_decode($object[0]['blackList']->bin,true);
			//print_r($objectDecoded);
			//print_r($submitedObject);

			if($objectDecoded[0]['nonce']<$submitedObject[0]['nonce'] && $objectDecoded[0]['hash']!=$submitedObject[0]['hash']){


				$blackList=array(
					"blackList"=>new MongoBinData($filterObj, MongoBinData::GENERIC)
				);
				$criteria=array("userId" => $userId,'modKey'=>hash('sha512',$modKey));

				if($user=Yii::app()->mongo->update('userObjects',$blackList,$criteria)){
					return 1;
				}

			}else if($objectDecoded[0]['nonce']>=$submitedObject[0]['nonce'] && $objectDecoded[0]['hash']!=$submitedObject[0]['hash']){
				return 2;

			}else if($objectDecoded[0]['hash']==$submitedObject[0]['hash'] ){
				return 3;
			}
		}


	}

	public function updatingFolderObj($folderObj,$modKey,$userId)
    {
        $criteria = array('userId' => $userId, 'modKey' => hash('sha512', $modKey));

        if ($newMails = Yii::app()->mongo->findOne('userObjects', $criteria)) {

        } else {
            return 3;
        }

        $submitedObject = json_decode($folderObj, true);

        foreach ($submitedObject as $index => $row)
            $mngData[] = array('index' => (int)$row['index']);

        if (!empty($mngData)) {
            $mngDataAgregate = array("userId" => $userId, '$or' => $mngData);


        if ($ref = Yii::app()->mongo->findAll('folderObj', $mngDataAgregate, array('_id' => 0, 'hash' => 1, 'index' => 1, 'nonce' => 1))) {

            foreach ($ref as $index => $row) {
                $objectDecoded[$row['index']] = $row;
            }
            unset($ref);
        }
        $count = 0;
        foreach ($submitedObject as $index => $row) {

            if (!isset($objectDecoded[$row['index']]) || $objectDecoded[$row['index']]['hash'] != $row['hash']) {
                if (!isset($objectDecoded[$row['index']]) || $row['nonce'] > $objectDecoded[$row['index']]['nonce']) {

                    $objectDecoded[$row['index']] = $row;

                    $folderObj = array(
                        "data" => $row['data'],
                        'index' => (int)$row['index'],
                        'hash' => $row['hash'],
                        'nonce' => $row['nonce'],
                        'userId' => $userId
                    );

                    $criteria = array("userId" => $userId, 'index' => (int)$row['index']);

                    if ($user = Yii::app()->mongo->upsert('folderObj', $folderObj, $criteria)) {
                        $count++;
                    } else {
                        return 2;
                    }

                }
            }
        }
    }
            if($count===0){
                return 3;
            }else{
                return 1;
            }




	}


	public function checkEmailsv2(){

		if($encryptedEmail=json_decode($this->seedEmails,true)){

			foreach($encryptedEmail as $row){

				if(!ctype_xdigit($row['mailQId']) || strlen($row['mailQId'])!=24){
					$this->addError('mailQId', 'notValid');
				}

				if(!ctype_xdigit($row['mailModKey']) || strlen($row['mailModKey'])!=32){
					$this->addError('mailModKey', 'notValid');
				}

				if(!ctype_xdigit($row['persFid']) || strlen($row['persFid'])!=24){
					$this->addError('persFid', 'notValid');
				}

				if(!ctype_xdigit($row['persFmodKey']) || strlen($row['persFmodKey'])!=32){
					$this->addError('persFmodKey', 'notValid');
				}

			}

		}else{
			$this->addError('emailData', 'notJson');
		}

	}

	public function checkEmailToDelete(){

		if($EmailToDelete=json_decode($this->emailToDelete,true)){
			foreach($EmailToDelete as $row) {

				if(!ctype_xdigit($row['id']) || strlen($row['id'])!=24){
					$this->addError('emailId', 'notValid');
				}

                if(count($row)!==3){
                    $this->addError('emailFields', 'notValid');
                }

				if(!ctype_xdigit($row['modKey']) || strlen($row['modKey'])>64){
					$this->addError('modKey', 'notValid');
				}
                if($row['v']!==2 && $row['v']!==3){
                    $this->addError('version', 'notValid');
                }

			}
		}else{
			$this->addError('emailToDelete', 'notJson');
		}

	}

	public function checkSeedEmails(){

		if($encryptedEmail=json_decode($this->seedEmails,true)){

			foreach($encryptedEmail as $row){

				if(!is_numeric($row['seedId'])){
					$this->addError('seedId', 'notValid');
				}
				if(!ctype_xdigit($row['seedModKey']) || strlen($row['seedModKey'])!=32){
					$this->addError('seedModKey', 'notValid');
				}

				if(!ctype_xdigit($row['mailQId']) || strlen($row['mailQId'])!=24){
					$this->addError('mailQId', 'notValid');
				}

				if(!ctype_xdigit($row['mailModKey']) || strlen($row['mailModKey'])!=32){
					$this->addError('mailModKey', 'notValid');
				}

				if(!ctype_xdigit($row['persFid']) || strlen($row['persFid'])!=24){
					$this->addError('persFid', 'notValid');
				}

				if(!ctype_xdigit($row['persFmodKey']) || strlen($row['persFmodKey'])!=32){
					$this->addError('persFmodKey', 'notValid');
				}

			}

		}else{
			$this->addError('emailData', 'notJson');
		}

	}

	public function checkDatas(){

		if($encryptedEmail=json_decode($this->emailData,true)){
			$SavingUserDataV2 = new SavingUserDataV2();

			$senderEmail=$SavingUserDataV2->extract_email_address(base64_decode($encryptedEmail['mailData']['meta']['from']))[0];


			$criteria=array('userId'=>Yii::app()->user->getId(),'addressHash'=>hash('sha512',$senderEmail),'active'=>1,'addr_type'=>array('$in'=>array(1,3)));


			if($newMails=Yii::app()->mongo->findOne('addresses',$criteria,array('addressHash'=>1))) {
			}else{
					$this->addError('email', 'notValid');
			}

			if(!ctype_xdigit($encryptedEmail['refId']) && strlen($encryptedEmail['refId'])!=24)
			{
				$this->addError('refId', 'notValid');
			}

			$totalRecipients=0;
			$totalRecipients+=count($encryptedEmail['mailData']['meta']['to']);
			$totalRecipients+=count($encryptedEmail['mailData']['meta']['cc']);
			$totalRecipients+=count($encryptedEmail['mailData']['meta']['bcc']);
			$this->totalRecipients=$totalRecipients;

			unset($param);

			if($limitsJSON=Yii::app()->mongo->findById('user',Yii::app()->user->getId(),array('planData'=>1,'pastDue'=>1))) {
				$limits = json_decode($limitsJSON['planData'], true);
				if ($limitsJSON['pastDue'] >0) {
					$this->addError('account', 'pastDue');
				}
				$sendLimits = $limits['recipPerMail'];
				if($totalRecipients>$sendLimits){
					$this->addError('recipPerMail', 'overLimit');
				}
			}

			if(isset($encryptedEmail['mailData']['attachments'])){
				foreach($encryptedEmail['mailData']['attachments'] as $index=>$fileData){

					if(strlen($fileData['fileName'])!=25 || !ctype_xdigit($fileData['fileName'])){
						$this->addError('attachments', 'notValid');
					}
					if(!isset($fileData['name']) || strlen($fileData['name'])>250){
						$this->addError('name', 'notValid');
					}
					if(!isset($fileData['size']) || strlen($fileData['size'])>250){
						$this->addError('size', 'notValid');
					}
					if(!isset($fileData['type']) || strlen($fileData['type'])>250){
						$this->addError('type', 'notValid');
					}
					if(!isset($fileData['modKey']) || strlen($fileData['modKey'])!=32){
						$this->addError('modKey', 'notValid');
					}

				}
			}


		}else{
			$this->addError('emailData', 'notJson');
		}

	}

	/**
	 * Save user email into json object and create attachment files copy with new name, old file belong to sender and new file for recipients
	 *
	 * @param {int} $userId
	 */
	public function sendEmailClearText($userId){

		$result['response']='fail';
		$encryptedEmail=json_decode($this->emailData,true);
		//print_r($encryptedEmail);

		$fileUpd=FileWorkerV2::makeCopiesWithMeta($encryptedEmail['mailData']['attachments'],$userId,strtotime('+1 year',time()));


		if($fileUpd!==false){

			$encryptedEmail['mailData']['attachments']=$fileUpd;

			$param[':email']=json_encode($encryptedEmail);
			$param[':modKey']=$encryptedEmail['modKey'];
			$param[':refId']=$encryptedEmail['refId'];
			$param[':destination']=1;

			$trans = Yii::app()->db->beginTransaction();


			if(Yii::app()->db->createCommand('INSERT INTO mail2sent (email,refId,modKey,destination) VALUES(:email,:refId,:modKey,:destination)')->execute($param))
			{

				$folderStatus=SavingUserDataV2::updatingFolderObj($this->folderData,$this->modKey,$userId);

				$stats=new StatsV2;
				$stats->counter('sentClearText');

				if($folderStatus==1){
					$result['response']='success';
					$result['data']='saved';
					$trans->commit();
				}else{

					$trans->rollback();
				}
			}

		}else{
			$result['data']='attachmentError';
		}


		//$result['response']='success';
		//$result['data']='saved';
		echo json_encode($result);


	}


	public function checkDatasPin(){

		if($encryptedEmail=json_decode($this->emailData,true))
		{
			$SavingUserDataV2 = new SavingUserDataV2();
			$senderEmail=$SavingUserDataV2->extract_email_address(base64_decode($encryptedEmail['sender']))[0];



			$criteria=array('userId'=>Yii::app()->user->getId(),'addressHash'=>hash('sha512',$senderEmail),'active'=>1,'addr_type'=>array('$in'=>array(1,3)));

			if($newMails=Yii::app()->mongo->findOne('addresses',$criteria,array('addressHash'=>1))) {
			}else{
                $this->addError('email', 'notValid');
			}

			if(!ctype_xdigit($encryptedEmail['refId']) && strlen($encryptedEmail['refId'])!==24)
			{
				$this->addError('refId', 'notValid');
			}

			if(!ctype_xdigit($encryptedEmail['modKey']) && strlen($encryptedEmail['modKey'])!==128)
			{
				$this->addError('modKey', 'notValid');
			}
			if(strlen($encryptedEmail['subject'])>300)
			{
				$this->addError('subject', 'tooLong');
			}

			if(strlen($encryptedEmail['pKeyHash'])!=64 || !ctype_xdigit($encryptedEmail['pKeyHash']))
			{
				$this->addError('pKeyHash', 'notValid');
			}

			$totalRecipients=0;
			$totalRecipients+=count($encryptedEmail['toCCrcpt']['recipients']);
			$totalRecipients+=count($encryptedEmail['bccRcpt']);
			$this->totalRecipients=$totalRecipients;

			unset($param);



			if($limitsJSON=Yii::app()->mongo->findById('user',Yii::app()->user->getId(),array('planData'=>1,'pastDue'=>1)))
			{
				$limits = json_decode($limitsJSON['planData'], true);
				if ($limitsJSON['pastDue'] >0) {
					$this->addError('account', 'pastDue');
				}
				$sendLimits = $limits['recipPerMail'];
				if($totalRecipients>$sendLimits){
					$this->addError('recipPerMail', 'overLimit');
				}
			}

			if(isset($encryptedEmail['attachments'])){
				foreach($encryptedEmail['attachments'] as $index=>$fileData){

					if(strlen($index)!=25 || !ctype_xdigit($index)){
						$this->addError('attachmentsName', 'notValid');
					}
					if(strlen($fileData)!=32 || !ctype_xdigit($fileData)){
						$this->addError('attachmentsModKey', 'notValid');
					}

				}
			}

		}else{
			$this->addError('emailData', 'notJson');
		}
		//$this->addError('emailData', 'notJson');
	}

	public function sendEmailWithPin($userId){

		$result['response']='fail';
		$encryptedEmail=json_decode($this->emailData,true);


		$param[':pKey']=$encryptedEmail['pKeyHash'];

		$param[':modKey']=$encryptedEmail['modKey'];
		$param[':refId']=$encryptedEmail['refId'];
		$param[':destination']=2;


		$fileSize=FileWorkerV2::makeCopiesWithModKey($encryptedEmail['attachments'],$userId,strtotime('+1 year',time()));

		if($fileSize!==false){
			$trans = Yii::app()->db->beginTransaction();

			$decEmail=json_decode($this->emailData,true);
			$decEmail['aSize']=$fileSize;
			$param[':email']=json_encode($decEmail);

			if(Yii::app()->db->createCommand('INSERT INTO mail2sent (email,refId,pKey,modKey,destination) VALUES(:email,:refId,:pKey,:modKey,:destination)')->execute($param)){


				$folderStatus=SavingUserDataV2::updatingFolderObj($this->folderData,$this->modKey,$userId);
				$stats=new StatsV2;
				$stats->counter('sentWithPin');

				//$folderStatus=1;
				if($folderStatus==1){
					$result['response']='success';
					$result['data']='saved';
					$trans->commit();
				}else{

					$trans->rollback();
				}
			}
		}

		echo json_encode($result);


	}



	public function checkDatasPGP(){

		if($encryptedEmail=json_decode($this->emailData,true)){
			$SavingUserDataV2 = new SavingUserDataV2();
			$senderEmail=$SavingUserDataV2->extract_email_address(base64_decode($encryptedEmail['sender']))[0];



			if($limitsJSON=Yii::app()->mongo->findById('user',Yii::app()->user->getId(),array('pastDue'=>1)))
			{
				if($limitsJSON['pastDue']>0){
					$this->addError('account', 'pastDue');
				}
			}

			$criteria=array('userId'=>Yii::app()->user->getId(),'addressHash'=>hash('sha512',$senderEmail),'active'=>1,'addr_type'=>array('$in'=>array(1,3)));

			if($newMails=Yii::app()->mongo->findOne('addresses',$criteria,array('addressHash'=>1))) {
			}else{
                $this->addError('email', 'notValid');
			}


			if(!ctype_xdigit($encryptedEmail['refId']) && strlen($encryptedEmail['refId'])!==24)
			{
				$this->addError('refId', 'notValid');
			}


			$totalRecipients=0;
			$totalRecipients+=count($encryptedEmail['toCCrcpt']['recipients']);
			$totalRecipients+=count($encryptedEmail['bccRcpt']);
			$totalRecipients+=count($encryptedEmail['bccRcptV1']);
			$totalRecipients+=count($encryptedEmail['toCCrcptV1']);
			$this->totalRecipients=$totalRecipients;

			unset($param);

			$param[':userId']=Yii::app()->user->getId();

			if($limitsJSON=Yii::app()->mongo->findById('user',Yii::app()->user->getId(),array('planData'=>1,'pastDue'=>1)))
			{
				$limits = json_decode($limitsJSON['planData'], true);
				if ($limitsJSON['pastDue'] >0) {
					$this->addError('account', 'pastDue');
				}
				$sendLimits = $limits['recipPerMail'];
				if($totalRecipients>$sendLimits){
					$this->addError('recipPerMail', 'overLimit');
				}
			}



			if(isset($encryptedEmail['toCCrcpt']) && count($encryptedEmail['toCCrcpt']['recipients'])>0){

				foreach($encryptedEmail['toCCrcpt']['recipients'] as $index=>$email64) {
					if(strlen($email64)>500){
						$this->addError('toCCrcpt:emailAddress', 'tooLong');
					}
				}
				if(strlen($encryptedEmail['toCCrcpt']['email'])>600000){
					$this->addError('toCCrcpt:email', 'tooLong');
				}
			}

			if(isset($encryptedEmail['toCCrcptV1'])){

				foreach($encryptedEmail['toCCrcptV1'] as $index=>$emailData){
					if(strlen($emailData['modKey'])!==128){
						$this->addError('toCCrcptV1:modKey', 'notValid');
					}
					if(strlen($emailData['key'])<=100 || strlen($emailData['key'])>1000){
						$this->addError('toCCrcptV1:key', 'notValid');
					}
					if(strlen($emailData['mail'])>600000){
						$this->addError('toCCrcptV1:mail', 'tooBig');
					}
					if(strlen($emailData['meta'])>6000){
						$this->addError('toCCrcptV1:meta', 'tooBig');
					}
					if(strlen($emailData['seedRcpnt'])>500){
						$this->addError('toCCrcptV1:seedRcpnt', 'notValid');
					}

				}

			}


			if(isset($encryptedEmail['bccRcptV1'])){

				foreach($encryptedEmail['bccRcptV1'] as $index=>$emailData){
					if(strlen($emailData['modKey'])!==128){
						$this->addError('bccRcptV1:modKey', 'notValid');
					}
					if(strlen($emailData['key'])<=100 || strlen($emailData['key'])>1000){
						$this->addError('bccRcptV1:key', 'notValid');
					}
					if(strlen($emailData['mail'])>600000){
						$this->addError('bccRcptV1:mail', 'tooBig');
					}
					if(strlen($emailData['meta'])>6000){
						$this->addError('bccRcptV1:meta', 'tooBig');
					}
					if(strlen($emailData['seedRcpnt'])>500){
						$this->addError('bccRcptV1:seedRcpnt', 'notValid');
					}

				}

			}


			if(isset($encryptedEmail['bccRcpt'])){
				foreach($encryptedEmail['bccRcpt'] as $email64=>$emailData){
					if(strlen($email64)>500 || strlen($emailData)>600000){
						$this->addError('bccRcpt', 'notValid');
					}
				}
			}

			if(!ctype_xdigit($encryptedEmail['modKey']) || strlen($encryptedEmail['modKey'])!==128){
				$this->addError('modKey', 'notValid');
			}

			if(strlen($encryptedEmail['subject'])>300){
				$this->addError('subject', 'tooLong');
			}


			if(isset($encryptedEmail['attachments'])){

				foreach($encryptedEmail['attachments'] as $index=>$fileData){

					if(strlen($index)>=25){
						$this->addError('attachmentsIndex', 'notValid');
					}


					if(strlen($fileData['base64'])!=1){
						$this->addError('attachment:base64', 'notValid');
					}
					if(strlen($fileData['name'])<10 || strlen($fileData['name'])>10000){
						$this->addError('attachment:name', 'notValid');
					}
					if(strlen($fileData['fileName'])!=25){
						$this->addError('attachment:fileName', 'notValid');
					}
					if(strlen($fileData['size'])>60){
						$this->addError('attachment:size', 'tooBig');
					}
					if(strlen($fileData['type'])<10 || strlen($fileData['type'])>10000){
						$this->addError('attachment:type', 'notValid');
					}
					if(strlen($fileData['modKey'])!=32){
						$this->addError('attachment:modKey', 'notValid');
					}


				}
			}

		}else{
			$this->addError('emailData', 'notJson');
		}
	}

	public function sendEmailPGP($userId){

		$result['response']='fail';
		$encryptedEmail=json_decode($this->emailData,true);


		$param[':email']=$this->emailData;
		//$param[':pKey']=$encryptedEmail['mailKey'];

		$param[':modKey']=$encryptedEmail['modKey'];
		$param[':refId']=$encryptedEmail['refId'];
		$param[':destination']=3;


		//print_r($encryptedEmail);

		//todo make 1 year for pgp and permanent for v1, not now
		$fileUpd=FileWorkerV2::makeCopiesWithMeta($encryptedEmail['attachments'],$userId,strtotime('+1 year',time()));


		if($fileUpd){
			$trans = Yii::app()->db->beginTransaction();

			if(Yii::app()->db->createCommand('INSERT INTO mail2sent (email,refId,modKey,destination) VALUES(:email,:refId,:modKey,:destination)')->execute($param)){

				$folderStatus=SavingUserDataV2::updatingFolderObj($this->folderData,$this->modKey,$userId);
				$stats=new StatsV2;
				$stats->counter('sentPGP');

				//$folderStatus=1;
				if($folderStatus==1){
				$result['response']='success';
				$result['data']='saved';
				$trans->commit();
				}else{

					$trans->rollback();
				}
			}
		}

		echo json_encode($result);

	}

	public function checkDatasInt(){

		if($encryptedEmail=json_decode($this->emailData,true)){

			//print_r($encryptedEmail);
			$SavingUserDataV2 = new SavingUserDataV2();
			$senderEmail=$SavingUserDataV2->extract_email_address(base64_decode($encryptedEmail['sender']))[0];


			$criteria=array('userId'=>Yii::app()->user->getId(),'addressHash'=>hash('sha512',$senderEmail),'active'=>1,'addr_type'=>array('$in'=>array(1,3)));

			if($newMails=Yii::app()->mongo->findOne('addresses',$criteria,array('addressHash'=>1))) {
			}else{
                $this->addError('email', 'notValid');
			}

			if(!ctype_xdigit($encryptedEmail['refId']) && strlen($encryptedEmail['refId'])!==24)
			{
				$this->addError('refId', 'notValid');
			}


			$totalRecipients=0;
			$totalRecipients+=isset($encryptedEmail['toCCrcpt']['recipients'])?count($encryptedEmail['toCCrcpt']['recipients']):0;
			$totalRecipients+=count($encryptedEmail['bccRcpt']);
			$totalRecipients+=count($encryptedEmail['bccRcptV1']);
			$totalRecipients+=count($encryptedEmail['toCCrcptV1']);
			$this->totalRecipients=$totalRecipients;

			unset($param);

			if($limitsJSON=Yii::app()->mongo->findById('user',Yii::app()->user->getId(),array('planData'=>1,'pastDue'=>1)))
			{
				$limits = json_decode($limitsJSON['planData'], true);


                if ($limitsJSON['pastDue'] >0) {
                    if(isset(json_decode($this->emailData,true)['toCCrcpt']['recipients'][0]) && count(json_decode($this->emailData,true)['toCCrcpt']['recipients'])==1){
                        $email=SavingUserDataV2::extract_email_address(base64_decode(json_decode($this->emailData,true)['toCCrcpt']['recipients'][0]))[0];

                        if(!in_array($email,Yii::app()->params['trustedSenders'])){
                            $this->addError('account', 'pastDue');
                        }

                    }else{
                        $this->addError('account', 'pastDue');
                    }
                }
				$sendLimits = $limits['recipPerMail'];
				if($totalRecipients>$sendLimits){
					$this->addError('recipPerMail', 'overLimit');
				}
			}



			if(isset($encryptedEmail['toCCrcpt']['recipients']) && count($encryptedEmail['toCCrcpt']['recipients'])>0){

				foreach($encryptedEmail['toCCrcpt']['recipients'] as $index=>$email64) {
					if(strlen($email64)>500){
						$this->addError('toCCrcpt:emailAddress', 'tooLong');
					}
				}
				if(strlen($encryptedEmail['toCCrcpt']['email'])>600000){
					$this->addError('toCCrcpt:email', 'tooLong');
				}
				if(strlen($encryptedEmail['toCCrcpt']['meta'])>6000){
					$this->addError('toCCrcpt:meta', 'tooLong');
				}
			}

			if(isset($encryptedEmail['toCCrcptV1'])){

				foreach($encryptedEmail['toCCrcptV1'] as $index=>$emailData){
					if(strlen($emailData['modKey'])!==128 && strlen($emailData['modKey'])!==32){
						$this->addError('toCCrcptV1:modKey', 'notValid');
					}
					if(strlen($emailData['key'])<=100 || strlen($emailData['key'])>1000){
						$this->addError('toCCrcptV1:key', 'notValid');
					}
					if(strlen($emailData['mail'])>600000){
						$this->addError('toCCrcptV1:mail', 'tooBig');
					}
					if(strlen($emailData['meta'])>6000){
						$this->addError('toCCrcptV1:meta', 'tooBig');
					}
					if(strlen($emailData['seedRcpnt'])>500){
						$this->addError('toCCrcptV1:seedRcpnt', 'notValid');
					}

				}

			}

			if(isset($encryptedEmail['bccRcptV1'])){

				foreach($encryptedEmail['bccRcptV1'] as $index=>$emailData){
					if(strlen($emailData['modKey'])!==128 && strlen($emailData['modKey'])!==32){
						$this->addError('bccRcptV1:modKey', 'notValid');
					}
					if(strlen($emailData['key'])<=100 || strlen($emailData['key'])>1000){
						$this->addError('bccRcptV1:key', 'notValid');
					}
					if(strlen($emailData['mail'])>600000){
						$this->addError('bccRcptV1:mail', 'tooBig');
					}
					if(strlen($emailData['meta'])>6000){
						$this->addError('bccRcptV1:meta', 'tooBig');
					}
					if(strlen($emailData['seedRcpnt'])>500){
						$this->addError('bccRcptV1:seedRcpnt', 'notValid');
					}

				}

			}

			if(isset($encryptedEmail['bccRcpt'])){
				foreach($encryptedEmail['bccRcpt'] as $email64=>$emailData){

					if(strlen($emailData['email'])>600000){
						$this->addError('bccRcpt:email', 'tooLong');
					}
					if(strlen($emailData['meta'])>6000){
						$this->addError('bccRcpt:meta', 'tooLong');
					}

				}
			}

			if(isset($encryptedEmail['attachments'])){
				foreach($encryptedEmail['attachments'] as $index=>$fileData){

					if(strlen($index)!=25 || !ctype_xdigit($index)){
						$this->addError('attachmentsName', 'notValid');
					}
					if(strlen($fileData)!=32 || !ctype_xdigit($fileData)){
						$this->addError('attachmentsModKey', 'notValid');
					}

				}
			}


			if(!ctype_xdigit($encryptedEmail['modKey']) || strlen($encryptedEmail['modKey'])!==128){
				$this->addError('modKey', 'notValid');
			}


		}else{
			$this->addError('emailData', 'notJson');
		}
		//$this->addError('emailData', 'notJson');
	}

	public function sendEmailInt($userId){

		$result['response']='fail';
		$encryptedEmail=json_decode($this->emailData,true);


		//$param[':pKey']=$encryptedEmail['mailKey'];

		$param[':modKey']=$encryptedEmail['modKey'];
		$param[':refId']=$encryptedEmail['refId'];
		$param[':destination']=4;

		$fileSize=FileWorkerV2::makeCopiesWithModKeyInt($encryptedEmail['attachments'],$userId,strtotime('+1 year',time()));

		if($fileSize!==false){
			$trans = Yii::app()->db->beginTransaction();

			$decEmail=json_decode($this->emailData,true);
			$decEmail['aSize']=$fileSize;
			$param[':email']=json_encode($decEmail);

			if(Yii::app()->db->createCommand('INSERT INTO mail2sent (email,refId,modKey,destination) VALUES(:email,:refId,:modKey,:destination)')->execute($param))
			{

				$folderStatus=SavingUserDataV2::updatingFolderObj($this->folderData,$this->modKey,$userId);
				$stats=new StatsV2;
				$stats->counter('sentInt');

				if($folderStatus==1){
				$result['response']='success';
				$result['data']='saved';
				$trans->commit();
				}else{

					$trans->rollback();
				}
			}
		}


		echo json_encode($result);

	}

	public function saveDraftEmail($userId){

		$result['response']='fail';

		$emailParse=json_decode($this->emailData,true);
		if(ctype_xdigit($emailParse['mailId']) && strlen($emailParse['mailId'])==24)
		{

			$fileSize=0;
			$files  = null;


			$person=array(
				"body" => new MongoBinData($emailParse['mail'], MongoBinData::GENERIC),
				"meta" => new MongoBinData($emailParse['meta'], MongoBinData::GENERIC),
				"modKey"=>hash('sha512',$emailParse['modKey']),
				"emailSize"=>strlen($emailParse['mail'])+$fileSize,
				"userId"=>$userId,
				"file"=>$files,
				"v"=>2
			);



			$criteria=array("_id" => new MongoId($emailParse['mailId']),'modKey'=>hash('sha512',$emailParse['modKey']));
			$unset=array("expireAfter"=>1);
			Yii::app()->mongo->unsetField('personalFolders',$unset,$criteria);

			//print_r($this->folderData);
			if($this->folderData!='{}'){
				//$trans = Yii::app()->db->beginTransaction();
				$folderStatus=SavingUserDataV2::updatingFolderObj($this->folderData,$this->modKey,$userId);

				if($folderStatus==1){

					if($message=Yii::app()->mongo->update('personalFolders',$person,$criteria))
					{

						$result['response']='success';
						$result['data']='saved';
						//$trans->commit();
					}else{

						//$trans->rollback();
					}

				}else{

					//$trans->rollback();
				}

			}else{

					if($message=Yii::app()->mongo->update('personalFolders',$person,$criteria))
					{
						$result['response']='success';
						$result['data']='saved';

					}

			}


		}


		echo json_encode($result);
	}


	public function deleteEmailV2($userId){
		$result['response']='fail';

        $EmailToDelete=json_decode($this->emailToDelete,true);

        if($res=Yii::app()->mongo->insert('email2delete',$EmailToDelete)){
            if(SavingUserDataV2::updatingFolderObj($this->folderData,$this->modKey,$userId)==1){
                $result['response']='success';
                $result['data']='saved';
            }
        }
/*
		if($EmailToDelete=json_decode($this->emailToDelete,true)){

			$allGoodV1=false;
			$allGoodV2=false;
			//split email to delete by version
			$emailV1=array();
			$emailV2=array();

			foreach($EmailToDelete as $email){
				if($email['v']===1){
					$emailV1[]=$email;
				}
				if($email['v']===2){
					$emailV2[]=$email;
				}
			}

				if(count($emailV1)>0){

					$files2Rem=array();
					foreach($emailV1 as $email) {
						if(!empty($email['modKey'])){
							$mngData[]=array('_id'=>new MongoId($email['id']),'modKey'=>hash('sha512',$email['modKey']));
						}else{
							$mngData[]=array('_id'=>new MongoId($email['id']),'userId'=>$userId);
						}

					}
					$mngDataAgregate=array('$or'=>$mngData);
					if($ref=Yii::app()->mongo->findAll('personalFolders',$mngDataAgregate,array('file'=>1,'v'=>1))){

						foreach($ref as $emData) {
							if(!empty($emData['file']) && $emData['file']!==null && $emData['file']!="null"){

								$file['name']=json_decode($emData['file']);

								$file['v']=isset($emData['v'])?$emData['v']:1; //old emails without version
								$files2Rem[]=$file;
								unset($file);
							}
						}
						if(is_array($mngDataAgregate)){
							Yii::app()->mongo->removeAll('personalFolders',$mngDataAgregate);
							FileWorkerV2::deleteFilesV1($files2Rem);
							$allGoodV1=true;
						}
					}else{
						//if nothing found should be deleted before but not synced
						$allGoodV1=true;
					}
				}else{
					$allGoodV1=true;
				}

				unset($ref,$mngData,$mngDataAgregate,$files2Rem);
				if(count($emailV2)>0){
					$files2Rem=array();

					foreach($emailV2 as $email) {
						$mngData[]=array('_id'=>new MongoId($email['id']),'modKey'=>hash('sha512',$email['modKey']));
						$mngDataAttachments[]=array('emailId'=>hash('sha256',$email['id'].$email['modKey']),'userId'=>$userId);
					}

					$mngDataAgregate=array('$or'=>$mngData);
					$mngDataAttachmentsAgregate=array('$or'=>$mngDataAttachments);

					//deleting emails
					if(is_array($mngDataAgregate)){
						Yii::app()->mongo->removeAll('personalFolders',$mngDataAgregate,array('pgpFileName'=>1,'file'=>1));
						$allGoodV2=true;
					}

					//delete attachments
					if(is_array($mngDataAttachmentsAgregate)){
						if($ref=Yii::app()->mongo->findAll('fileToObj',$mngDataAttachmentsAgregate)){

							//Yii::log(CVarDumper::dumpAsString($ref), 'vardump', 'system.web.CController22');

							foreach($ref as $emData) {
								if(!empty($emData['pgpFileName'])){
									$files2Rem[]=$emData['pgpFileName'];
								}
							}
							//print_r($mngDataAgregate);
							//Yii::app()->end();
							FileWorkerV2::deleteFilesV2($files2Rem);
							Yii::app()->mongo->removeAll('fileToObj',$mngDataAttachmentsAgregate);

							unset($ref);
						}
					}

					unset($mngData,$mngDataAgregate,$files2Rem);

				}else{
					$allGoodV2=true;
				}
				//$result['response']='success';
				//$result['data']='saved';


			if($allGoodV2 && $allGoodV1){
				if(SavingUserDataV2::updatingFolderObj($this->folderData,$this->modKey,$userId)==1){
					$result['response']='success';
					$result['data']='saved';
				}
			}

		}*/

		echo json_encode($result);
	}

	public function saveNewEmailV2($userId){
		$result['response']='fail';


		if($emailData=json_decode($this->seedEmails,true)) {

			foreach($emailData as $index=> $emData){
				$mngData[]=array('_id'=>new MongoId($emData['mailQId']),'modKey'=>hash('sha512',$emData['mailModKey']));
				$reformatSeedData[$emData['mailQId']]=$emData;
			}

			$mngDataAgregate=array('$or'=>$mngData);
			//print_r($mngDataAgregate);

			if($ref=Yii::app()->mongo->findAll('mailQv2',$mngDataAgregate)){
				//print_r($ref);
				//$trans = Yii::app()->db->beginTransaction();

				$saveSwitch=true;
				$folderStatus=SavingUserDataV2::updatingFolderObj($this->folderData,$this->modKey,$userId);

				//$folderStatus=1;

				if($folderStatus==1){

					foreach($ref as $id=>$row){
						if($saveSwitch){
							$body=new MongoBinData($row['body'], MongoBinData::GENERIC);
							//$meta=new MongoBinData($row['meta'], MongoBinData::GENERIC);
							$vers=2;

							$newPersonalFolderMessages=array(
								"body" => $body,
								//"meta" => $meta,

								"modKey"=>hash('sha512',$reformatSeedData[$id]['persFmodKey']), //modKey will in criteria
								"emailSize"=>$row['emailSize'],
								"userId"=>$userId, //in criteria
								//"file"=>json_encode($files), //use old style for version 1, delete file from personal folder documents, new version will have fileToObj reference
								"v"=>$vers
							);


							$criteria=array("_id" => new MongoId($reformatSeedData[$id]['persFid']),'modKey'=>hash('sha512',$reformatSeedData[$id]['persFmodKey']));

							if($message=Yii::app()->mongo->update('personalFolders',$newPersonalFolderMessages,$criteria))
							{
								$unset=array("expireAfter"=>1);
								Yii::app()->mongo->unsetField('personalFolders',$unset,$criteria);

								$files=FileWorkerV2::makeFilesCopyV2($row['file'],$userId,$reformatSeedData[$id]['persFid'],$reformatSeedData[$id]['persFmodKey']);

								$saveSwitch=true;
							}else{
								$saveSwitch=false;
								break;
							}


						}
					}
					if($saveSwitch){
						Yii::app()->mongo->removeAll('mailQv2',$mngDataAgregate);

						$result['response']='success';
						$result['data']='saved';
						//$trans->commit();
					}else{
						//$trans->rollback();
						$result['response']='fail';
						unset($result['data']);
					}

				}else{

					//$trans->rollback();
				}



			}
		}


		echo json_encode($result);

	}

	public function saveNewEmailOld($userId){

		/*
		 * Transaction with folder obj
		 * if good, move files to mongo and remove seed with mailq reference, and make copy for file - crawler
		 */

		$result['response']='fail';

		if($emailData=json_decode($this->seedEmails,true)){

			foreach($emailData as $index=> $emData){
				$mngData[]=array('_id'=>new MongoId($emData['mailQId']),'modKey'=>hash('sha512',$emData['mailModKey']));
				$reformatSeedData[$emData['mailQId']]=$emData;
			}


			$mngDataAgregate=array('$or'=>$mngData);

			if($ref=Yii::app()->mongo->findAll('mailQueue',$mngDataAgregate)){

				//$trans = Yii::app()->db->beginTransaction();

				$folderStatus=SavingUserDataV2::updatingFolderObj($this->folderData,$this->modKey,$userId);
				//$folderStatus=0;

				if($folderStatus==1){
					$result['response']='success';
					$result['data']='saved';

					foreach($ref as $id=>$row){
						//new version old emails creating file copy when user fetch email
						if(strlen($row['oldId'])==24){
							$files=FileWorkerV2::makeFilesCopy($row['file'],$userId);

							$body=new MongoBinData($row['body'], MongoBinData::GENERIC);
							$meta=new MongoBinData($row['meta'], MongoBinData::GENERIC);
							$vers=15;
						}else{
							$body=new MongoBinData($row['body']->bin, MongoBinData::GENERIC);
							$meta=new MongoBinData($row['meta']->bin, MongoBinData::GENERIC);
							$files=$row['file'];
							$vers=1;
						}
						//FileWorkerV2::getFileSize
						//$fSize=is_array(json_decode($row['file']))?array_sum(array_map("FileWorkerV2::getFileSize",json_decode($row['file']))):0;

						//$meta=substr(hex2bin($row['meta']),0,16).substr(hex2bin($row['meta']),16);
						//$body=substr(hex2bin($row['body']),0,16).substr(hex2bin($row['body']),16);

						$newPersonalFolderMessages=array(
							"body" => $body,
							"meta" => $meta,
							//"meta" => new MongoBinData($meta, MongoBinData::GENERIC),
							//"body" => new MongoBinData($body, MongoBinData::GENERIC),

							"modKey"=>hash('sha512',$reformatSeedData[$id]['persFmodKey']), //modKey will in criteria
							"emailSize"=>$row['emailSize'],
							"userId"=>$userId, //in criteria
							"file"=>$files, //use old style for version 1, delete file from personal folder documents, new version will have fileToObj reference
							"v"=>$vers
						);
						$criteria=array("_id" => new MongoId($reformatSeedData[$id]['persFid']),'modKey'=>hash('sha512',$reformatSeedData[$id]['persFmodKey']));

						if($message=Yii::app()->mongo->update('personalFolders',$newPersonalFolderMessages,$criteria))
						{



						}else{
							//$trans->rollback();
							$result['response']='fail';
							unset($result['data']);
							break;
						}
					}
					//$trans->commit();

				//delete mailQue rows
					if (! function_exists('array_column')) {
						function array_column(array $input, $columnKey, $indexKey = null) {
							$array = array();
							foreach ($input as $value) {
								if ( ! isset($value[$columnKey])) {
									trigger_error("Key \"$columnKey\" does not exist in array");
									return false;
								}
								if (is_null($indexKey)) {
									$array[] = $value[$columnKey];
								}
								else {
									if ( ! isset($value[$indexKey])) {
										trigger_error("Key \"$indexKey\" does not exist in array");
										return false;
									}
									if ( ! is_scalar($value[$indexKey])) {
										trigger_error("Key \"$indexKey\" does not contain scalar value");
										return false;
									}
									$array[$value[$indexKey]] = $value[$columnKey];
								}
							}
							return $array;
						}
					}

				$foundArray=array_column($ref, '_id');

					if(is_array($foundArray)){

				foreach($foundArray as $i=>$perfId){
					$mngData[]=array('_id'=>new MongoId($perfId),'modKey'=>hash('sha512',$reformatSeedData[$perfId]['mailModKey']));

					$param[":id_$i"]=$reformatSeedData[$perfId]['seedId'];
					$param[":modKey_$i"]=hash('sha512',$reformatSeedData[$perfId]['seedModKey']);
					$par[] = "(:id_$i,:modKey_$i)";
				}
					$mngData2Remove=array('$or'=>$mngData);

					if (Yii::app()->db->createCommand("DELETE FROM seedTable WHERE (id,modKey) IN (" . implode($par, ',') . ")")->execute($param))
					{
						Yii::app()->mongo->removeAll('mailQueue',$mngData2Remove);
					}
				}

				}else{

					//$trans->rollback();
				}


			}

		}
		echo json_encode($result);


	}



	public function folderSettings($userId){

	//	print_r($this->filterData);
		$proceed=true;
		$result['response']='success';
		//$trans = Yii::app()->db->beginTransaction();

		if(!empty($this->filterData)){
			$filterStatus=SavingUserDataV2::updatingFilterObj($this->filterData,$this->modKey,$userId);

			if($filterStatus==2){
				$proceed=false;
				//$trans->rollback();
			}

		}
		if($proceed){
			$folderStatus=SavingUserDataV2::updatingFolderObj($this->folderData,$this->modKey,$userId);

			if($folderStatus==1){
				$result['response']='success';
				$result['data']='saved';
				//$trans->commit();
				//$trans->rollback();
			}else if($folderStatus==2){
				$result['response']='success';
				$result['data']='newerFound';
				//$trans->rollback();
			}else if($folderStatus==3){
				//$trans->rollback();
				$result['response']='success';
				$result['data']='nothingUpdt';
			}
		}

		//print_r($this->folderData);

		echo json_encode($result);
	}

	public function savingUserObjWdeletePGP($userId)
	{
		$result['response']='fail';

		$param[':addressHash']=$this->email;
		$param[':userId']=$userId;
		$param[':addr_type']=1;
		//$trans = Yii::app()->db->beginTransaction();

		$addressObj=array(
			"active"=>0,
			"retentionStarted"=>new MongoDate(strtotime('now')),
		);


		$criteria=array("userId" =>$userId,"addressHash"=>$this->email,"addr_type"=>array('$ne'=>1));

		if(Yii::app()->mongo->update('addresses',$addressObj,$criteria)){

			$status=SavingUserDataV2::updatingUserObj($this->objectData,$this->modKey,$userId);

			if($status==1){
				$result['response']='success';
				$result['data']='saved';
				//$trans->commit();
			}else if($status==2){
				$result['response']='success';
				$result['data']='newerFound';
				//$trans->rollback();
			}else if($status==3){
				//$trans->rollback();
				$result['response']='success';
				$result['data']='nothingUpdt';
			}


		}else{
			//$trans->rollback();
		}

		echo json_encode($result);
	}


	public function checkPGP($key){

		$rr=Yii::app()->basePath.'/pgps/'.hash('sha256',openssl_random_pseudo_bytes(16));
		mkdir($rr, 0777);
		putenv("GNUPGHOME=$rr");
		$gpg = new gnupg();
		$gpg->seterrormode(gnupg::ERROR_EXCEPTION);
		try {
			$keys = $gpg->import($key);
			if($keys['imported']===0 ){
				exec("rm -rf {$rr}");
				return false;
			}else{
				exec("rm -rf {$rr}");
				return true;
			}


		} catch (Exception $e) {
			exec("rm -rf {$rr}");
			return false;
		}

	}

	public function savingUserObjWnewPGPkeys($userId){
		/*
		 * Need to check:
		 * 0) PGP Key is correct
		 * 1) Account Past Due
		 * 2) Allowed PGP Length
		 * 5) Already Exist
		 * 6)domain is correct
		 */

		$result['response']='fail';
		$good=true;

		if($userPlanString=Yii::app()->mongo->findById('user',$userId,array('planData'=>1,'pastDue'=>1)))
		{
				$userPlanArray=json_decode($userPlanString['planData'],true);
/*
				if(!SavingUserDataV2::checkPGP(base64_decode($this->publicKey))){
					$good=false;
					$result['data']='pgpWrong';
				}*/
				//+0

				/*try{
					$res_pubkey = openssl_pkey_get_public(base64_decode($this->publicKey));
					$pubkeyBits = openssl_pkey_get_details($res_pubkey);
				} catch (Exception $e) {
					$good=false;
					$result['data']='pgpWrong';
				}*/

				//+1
				if($good && $userPlanString['pastDue']>0){
					$result['data']='pastdue';
					$good=false;
				}


				//+2
			/*	if($good && $pubkeyBits['bits']>$userPlanArray['pgpStr']){
					$result['data']='keyOverStrong';
					$good=false;
				}*/


				//+5

				$criteria=array('userId'=>$userId,'addressHash'=>hash('sha512',$this->email),'active'=>1);

				if($good && !Yii::app()->mongo->findOne('addresses',$criteria,array('addressHash'=>1))) {

					$result['data']='notExist';
					$good=false;
				}



		}else{
			$good=false;
			$result['data']='notExist3';
		}

		if($good){
			//$trans = Yii::app()->db->beginTransaction();

			$addressObj=array(
				"mailKey"=>$this->publicKey,

			);

			$criteria=array("userId" => $userId,'addressHash'=>hash('sha512',strtolower($this->email)));


			if($user=Yii::app()->mongo->update('addresses',$addressObj,$criteria)){

				$status=SavingUserDataV2::updatingUserObj($this->objectData,$this->modKey,$userId);

				if($status==1){
					$result['response']='success';
					$result['data']='saved';
					//$trans->commit();
				}else if($status==2){
					$result['response']='success';
					$result['data']='newerFound';
					//$trans->rollback();
				}else if($status==3){
					//$trans->rollback();
					$result['response']='success';
					$result['data']='nothingUpdt';
				}


			}else{
				//$trans->rollback();
				$result['response']='success';
				$result['data']='nothingUpdt';
			}

		}


		echo json_encode($result);
	}

	public function savingUserObjWnewPGP($userId)
	{
		/*
		 * Need to check:
		 * 0) PGP Key is correct
		 * 1) Account Past Due
		 * 2) Allowed PGP Length
		 * 3) Allowed Alias or Disposable count
		 * 4) Correct domain for mail
		 * 5) Not Already Exist
		 *
		 */

		$result['response']='fail';
		$good=true;



		if($userPlanString=Yii::app()->mongo->findById('user',$userId,array('planData'=>1,'pastDue'=>1)))
		{
			$userPlanArray=json_decode($userPlanString['planData'],true);

			//+0
			/*if(!SavingUserDataV2::checkPGP(base64_decode($this->publicKey))){
				$good=false;
				$result['data']='pgpWrong';
			}*/

			//+1
			if($good && $userPlanString['pastDue']>0){
				$result['data']='pastdue';
				$good=false;
			}

/*
			//+2
			if($good && $pubkeyBits['bits']>$userPlanArray['pgpStr']){
				$result['data']='keyOverStrong';
				$good=false;
			}
			*/

			//+3


			//findAll($collectionName,$data,$selectFields=array(),$limit=null)

			$alias=0;
			$criteria=array('userId'=>$userId,'addr_type'=>3,'active'=>1);
			if($al=Yii::app()->mongo->findAll('addresses', $criteria, array('addressHash' => 1))) {
				$alias=count($al);
			}
			$dispos=0;
			$criteria=array('userId'=>$userId,'addr_type'=>2,'active'=>1);
			if($disp=Yii::app()->mongo->findAll('addresses', $criteria, array('addressHash' => 1))) {
				$dispos=count($disp);
			}

			if($good){

				if($this->type==2){
					//disp
					if($good && $dispos >= $userPlanArray['dispos']){
						$result['data']='emailAdOverLimit';
						$good=false;
					}

				}else if($good && $this->type==3){
					//alias
					if($alias >= $userPlanArray['alias']){
						$result['data']='emailAdOverLimit';
						$good=false;
					}
				}


			}else{
				$good=false;
			}

			//+4

			if($good && $domains=Yii::app()->db->createCommand("SELECT id,domain FROM virtual_domains WHERE globalDomain=1 OR (userId=\"$userId\" AND availableForAliasReg=1)")->queryAssoc('domain')){
				$dom=explode('@',strtolower($this->email));

				$reDom=CustomDomainV2::get_domain($dom[1]);

				if (in_array($reDom, array_keys($domains))){

				}else{
					$result['data']='domainUnavail';
					$good=false;
				}

			}

			//+5
			if($good && CheckIfExistV2::validateEmail($userId,true)==='false') {
				$result['data']='alrdExist';
				$good=false;
			}

		}else{
			$good=false;
		}

		if($good){

			//use upsert 
			$userObj=array(
				"addr_type"=>(int)$this->type,
				"mailKey"=>$this->publicKey,
				"vdId"=>$domains[$reDom]['id'],
				"active"=>1,
				"addressHash"=>hash('sha512',strtolower($this->email)),
				"userId"=>$userId,
				"v"=>2

			);
			$criteria=array("addressHash"=>hash('sha512',strtolower($this->email)));

			$unset=array("retentionStarted"=>1);

			Yii::app()->mongo->unsetField('addresses',$unset,$criteria);

			if(Yii::app()->mongo->upsert('addresses',$userObj,$criteria)){

				$status=SavingUserDataV2::updatingUserObj($this->objectData,$this->modKey,$userId);

				if($status==1){
					$result['response']='success';
					$result['data']='saved';
				//	$trans->commit();
				}else if($status==2){
					$result['response']='success';
					$result['data']='newerFound';
					//$trans->rollback();
				}else if($status==3){
					//$trans->rollback();
					$result['response']='success';
					$result['data']='nothingUpdt';
				}


				}else{
					//$trans->rollback();
				}




		}


		echo json_encode($result);

	}

	public function updatingUserObj($userObj,$modKey,$userId)
	{

		if($object=Yii::app()->mongo->findByUserIdNew('userObjects', $userId, array('userObj' => 1))){



		//if($object=Yii::app()->db->createCommand("SELECT userObj FROM user WHERE id=$userId")->queryScalar()){

			$submitedObject=json_decode($userObj,true);
			$objectDecoded=json_decode($object[0]['userObj']->bin,true);
			//print_r($objectDecoded);
			//print_r($submitedObject);

			if($objectDecoded[0]['nonce']<$submitedObject[0]['nonce'] && $objectDecoded[0]['hash']!=$submitedObject[0]['hash']){

				$userObj=array(
					"userObj"=>new MongoBinData($this->objectData, MongoBinData::GENERIC)
				);
				$criteria=array("userId" => $userId,'modKey'=>hash('sha512',$modKey));

				if(Yii::app()->mongo->update('userObjects',$userObj,$criteria)){
					return 1;
				}

			}else if($objectDecoded[0]['nonce']>=$submitedObject[0]['nonce'] && $objectDecoded[0]['hash']!=$submitedObject[0]['hash']){
				return 2;

			}else if($objectDecoded[0]['hash']==$submitedObject[0]['hash'] ){
				return 3;
			}
		}

	}


	public function deleteDomain($userId)
	{

		$result['response']='fail';

		//$trans = Yii::app()->db->beginTransaction();

		$CustomDomainV2=new CustomDomainV2();

		$cDomStatus = $CustomDomainV2->deleteDomain($userId,$this->domain);

		if($cDomStatus==1){
			$status = SavingUserDataV2::updatingProfileObj($this->objectData,$this->modKey,$userId);

			if($status==1){
				//$trans->commit();
				$result['response']='success';
				$result['data']='saved';
			}else{
				//echo  'dfsdfsdfdsf';
				//$trans->rollback();
			}

		}else if($cDomStatus==2){

			//$trans->rollback();
		}else if($cDomStatus==3){

			//$trans->rollback();
		}

		echo json_encode($result);

	}


	public function updateDomain($userId)
	{
		$result['response']='fail';

		$trans = Yii::app()->db->beginTransaction();

		$cDomStatus = CustomDomainV2::vrfOwnership($userId,$this->domain,$this->vrfString);

		if($cDomStatus==1) {
			$status = SavingUserDataV2::updatingProfileObj($this->objectData, $this->modKey, $userId);

			if ($status == 1) {
				//$trans->commit();
				$result['response'] = 'success';
				$result['data'] = 'saved';
			}

		}

		echo json_encode($result);

	}


	public function savePendingDomain($userId)
	{
		$result['response']='fail';

		$trans = Yii::app()->db->beginTransaction();

		$cDomStatus = CustomDomainV2::addPending($userId,$this->domain,$this->vrfString);
		if($cDomStatus==1){
			$status = SavingUserDataV2::updatingProfileObj($this->objectData,$this->modKey,$userId);

				if($status==1){
					$trans->commit();
					$result['response']='success';
					$result['data']='saved';
				}else{
					//echo  'dfsdfsdfdsf';
					$trans->rollback();
				}

		}else if($cDomStatus==2){
			//echo  'dfsdfsdfdsf2';
			$trans->rollback();
		}else if($cDomStatus==3){
			//echo  'dfsdfsdfdsf3';
			$trans->rollback();
		}

		echo json_encode($result);
	}


	public function updatingProfileObj($profObj,$modKey,$userId){


		if($object=Yii::app()->mongo->findByUserIdNew('userObjects', $userId, array('profileSettings' => 1))){
			//print_r($object);

			//new MongoBinData($this->profileObject, MongoBinData::GENERIC),


			$submitedObject=json_decode($profObj,true);
			$objectDecoded=json_decode($object[0]['profileSettings']->bin,true);

			if($objectDecoded[0]['nonce']<$submitedObject[0]['nonce'] && $objectDecoded[0]['hash']!=$submitedObject[0]['hash']){



				//print_r($objectDecoded);
				$profileS=array(
					"profileSettings"=>new MongoBinData($profObj, MongoBinData::GENERIC)
				);
				$criteria=array("userId" => $userId,'modKey'=>hash('sha512',$modKey));

				if(Yii::app()->mongo->update('userObjects',$profileS,$criteria)){
					return 1;
				}

			}else if($objectDecoded[0]['nonce']>=$submitedObject[0]['nonce'] && $objectDecoded[0]['hash']!=$submitedObject[0]['hash']){
				//echo '22222';
				return 2;
			}else if($objectDecoded[0]['hash']==$submitedObject[0]['hash'] ){
				//echo '33333';
				return 3;
			}
		}
	}


	public function saveObjects($userId)
	{
		//print_r($this->objectName);
		//print_r($this->objectData);
		//print_r($this->modKey);

		$result['response']='fail';
		switch($this->objectName){
			case('profObj') :
				//print_r($this->objectName);
				//print_r($this->objectData);

				$status=SavingUserDataV2::updatingProfileObj($this->objectData,$this->modKey,$userId);

				if($status==1){
					$result['response']='success';
					$result['data']='saved';

				}else if($status==2){
					$result['response']='success';
					$result['data']='newerFound';

				}else if($status==3){
					$result['response']='success';
					$result['data']='nothingUpdt';
				}


				break;

			case('userObj') :
				//print_r($this->objectName);
				//print_r($this->objectData);

				$status=SavingUserDataV2::updatingUserObj($this->objectData,$this->modKey,$userId);

				if($status==1){
					$result['response']='success';
					$result['data']='saved';

				}else if($status==2){
					$result['response']='success';
					$result['data']='newerFound';

				}else if($status==3){
					$result['response']='success';
					$result['data']='nothingUpdt';
				}

				break;

			case('contObj') :
				//print_r($this->objectName);
				//print_r($this->objectData);

				$status=SavingUserDataV2::updatingContactObj($this->objectData,$this->modKey,$userId);

				if($status==1){
					$result['response']='success';
					$result['data']='saved';

				}else if($status==2){
					$result['response']='success';
					$result['data']='newerFound';

				}else if($status==3){
					$result['response']='success';
					$result['data']='nothingUpdt';
				}

				break;

			case('filterObj') :
				//print_r($this->objectName);
				//print_r($this->objectData);

				$status=SavingUserDataV2::updatingFilterObj($this->objectData,$this->modKey,$userId);

				if($status==1){
					$result['response']='success';
					$result['data']='saved';

				}else if($status==2){
					$result['response']='success';
					$result['data']='newerFound';

				}else if($status==3){
					$result['response']='success';
					$result['data']='nothingUpdt';
				}

				break;



	}
		echo json_encode($result);

	}

	public function updatingContactObj($contactObj,$modKey,$userId){


		if($object=Yii::app()->mongo->findByUserIdNew('userObjects', $userId, array('contacts' => 1))){
		//if($object=Yii::app()->db->createCommand("SELECT contacts FROM user WHERE id=$userId")->queryScalar()){

			$submitedObject=json_decode($contactObj,true);
			$objectDecoded=json_decode($object[0]['contacts']->bin,true);
			//print_r($objectDecoded);
			//print_r($submitedObject);

			if($objectDecoded[0]['nonce']<$submitedObject[0]['nonce'] && $objectDecoded[0]['hash']!=$submitedObject[0]['hash']){

				$contactsObj=array(
					"contacts"=>new MongoBinData($contactObj, MongoBinData::GENERIC)
				);
				$criteria=array("userId" => $userId,'modKey'=>hash('sha512',$modKey));

				if(Yii::app()->mongo->update('userObjects',$contactsObj,$criteria)){
					return 1;
				}

			}else if($objectDecoded[0]['nonce']>=$submitedObject[0]['nonce'] && $objectDecoded[0]['hash']!=$submitedObject[0]['hash']){
				//echo '22222';
				return 2;
			}else if($objectDecoded[0]['hash']==$submitedObject[0]['hash'] ){
				//echo '33333';
				return 3;
			}
		}
	}


}