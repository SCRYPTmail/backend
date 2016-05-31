<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class SiteController extends Controller
{
	public $data, $baseUrl;
	public $fileVers='0580';

	public function beforeAction($action)
	{
		/*
		if(strtolower($this->action->Id)!='acceptemailfrompostfix'){
			$this->render('moving');
			Yii::app()->end();
		}else{
			throw new CHttpException(403,"Damn You!, you are not authorized to perform this action.");
		}

		Yii::app()->end();
*/
//print_r(parent::beforeAction($action));
		$userAction=strtolower($this->action->Id);

		$model = new CheckLimitsV2();
		$model->userAction = $userAction;

		if ($model->validate()){

			if($model->checkAllowedAction()===true){
				$this->baseUrl = Yii::app()->baseUrl;
				return true;
			}else if($model->checkAllowedAction()==="pastDue"){
				$result['response']="fail";
				$result['data']="pastDue";
				echo json_encode($result);
				return false;

			}else{
				$result['response']="fail";
				$result['data']="limitIsReached";
				echo json_encode($result);
				return false;
			}
		}else{
			echo json_encode($model->getErrors());
			return false;
		}


		//if (parent::beforeAction($action)) {


		//}
		//return false;

	}

	public function filters()
	{
		return array(
			'accessControl', // perform access control for actions
		);
	}

	public function accessRules()
	{
		return array(
			array('allow', // allow all users to perform 'index' and 'view' actions
				'actions' => array(
					'loginUserV2',
					'logoutV2',
					'callBackBitcoinV2',
					'callBackPayPalV2',
					'crawler1',
					'dFV2',
					'retrieveUnregEmailV2',
					'downloadFileUnregV2',
					'deleteEmailUnregV2',
					'retrievePublicKeyUnregV2',
					'sendEmailUnregV2',
					'getRawUserDataV2',
					'checkStepsV2',
					'checkTokenHashesV2',
					'changeLoginPassV2',
					'checkTokenV2',
					'checkLoginTokenV2',
					'createNewUserV2',
					'resetUserObjectV2',
					'checkEmailExistV2',
					'cleanUp',
					'resetUserV2',
					'resetUserTwoStepV2',

					'deployV2',
					'safeBox',
					'error',

					'CheckMongo'

				),
				'expression' => 'Yii::app()->user->role["role"]==0'
			),
			array('allow', // allow all users to perform 'index' and 'view' actions
				'actions' => array(
					'composeMailUnreg',
					'sendLocalMessageUnreg',
					'getFile',
					'retrievePublicKeys',
					'deleteMessageUnreg',
					'checkDomain'
				),
				'expression' => 'Yii::app()->session["unregisteredLogin"]'
			),
			array('allow', // allow all users to perform 'index' and 'view' actions
				'actions' => array(
					'loginUserV2',
					'logoutV2',
					'getRawUserDataV2',
					'updateUserDataV2',
					'getUserObjCheckSumV2',
					'getObjByIndexV2',
					'updateObjectsV2_obsolete',
					'checkEmailExistV2',
					'checkIfPasswordOkV2',
					'setup2FacV2',
					'checkDomainExistV2',
					'savePendingDomainV2',
					'retCustomDomainUserV2',
					'checkKeyUniqueV2',
					'retrievePlanPricingV2',
					'createOrderBitcoinV2',
					'calculatePriceV2',
					'retrieveUserPlanV2',
					'savePlanV2',
					'callBackBitcoinV2',
					'callBackPayPalV2',
					'retrieveFoldersMetaTempV2',
					'retrieveMessageV2',
					'getTrustedSendersV2',
					'getPublicKeysV2',
					'emailsOwnershipsV2',
					'getDraftMessageIdV2',
					'savingUserObjectsV2',
					'changePassV2',
					'changePassOneStepV2',
					'changeSecondPassV2',
					'saveGoogleAuthV2',
					'savingUserObjWnewPGPV2',
					'availableDomainsForAliasV2',
					'savingUserObjWdeletePGPV2',
					'deleteDomainV2',
					'folderSettingsV2',
					'savingUserObjWnewPGPkeysV2',
					'deleteUserV2',
					'saveDraftEmailV2',
					'retrievePublicKeysV2',
					'saveNewAttachmentV2',
					'removeFileFromDraftV2',
					'downloadFileV2',
					'downloadFileUnregV2',
					'sendEmailClearTextV2',
					'sendEmailWithPinV2',
					'sendEmailPGPV2',
					'sendEmailIntV2',
					'crawler1',
					'getNewSeedsV2',
					'getNewMetaV2',
					'saveNewEmailOldV2',
					'dFV2',
					'saveNewEmailV2',
					'deleteEmailV2',
					'retrieveUnregEmailV2',
					'deleteEmailUnregV2',
					'retrievePublicKeyUnregV2',
					'sendEmailUnregV2',
					'checkStepsV2',
					'checkTokenV2',
					'checkLoginTokenV2',
					'createNewUserV2',
					'resetUserObjectV2',
					'claimFreeV2',
					'assignTypesV2',
					'updateSecretTokenV2',
					'resetUserTwoStepV2',
					'updateDomainV2',

					'CheckMongo',

					'getSafeBoxList',
					'safeBox',
					'deleteFileFromSafe',
					'error',
					'index',


				),
				'expression' => 'Yii::app()->user->role["role"]!=0'
			),
			array('deny', // deny all users
				'users' => array('*'),
			),
		);
	}

	public function missingAction($actionID)
	{
		$this->renderPartial('404');
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */

	//===================
	//new Api Calls
	public function actionLoginUserV2(){

		$model = new loginUserV2();

		// collect user input data
			$model->attributes = $_POST;

			if ($model->validate()){
				$model->login();
			}else
				echo json_encode($model->getErrors());

	}

	public function actionLogoutV2()
	{
		Yii::app()->user->logout();
		$result['response']="success";
		echo json_encode($result);
	}


	public function actionCheckTokenV2()
	{
		$model = new ResetAccountV2('checkToken');
		$model->attributes = $_POST;
		if ($model->validate()){
			$model->checkToken();
		}else
			echo json_encode($model->getErrors());
	}


	public function actionCreateNewUserV2()
	{
		$model = new ResetAccountV2('createNewUser');
		$model->attributes = $_POST;
		if ($model->validate()){
			$model->createNewUser();
		}else
			echo json_encode($model->getErrors());
	}

	public function actionResetUserV2()
	{
		$model = new ResetAccountV2('resetUser');
		$model->attributes = $_POST;
		if ($model->validate()){
			$model->resetUser();
		}else
			echo json_encode($model->getErrors());
	}

	public function actionResetUserTwoStepV2()
	{
		$model = new ResetAccountV2('resetUserTwoStep');
		$model->attributes = $_POST;
		if ($model->validate()){
			$model->resetUserTwoStep();
		}else
			echo json_encode($model->getErrors());
	}

	public function actionDeployV2()
	{

		$hashed=hash('sha256',Yii::app()->getRequest()->getQuery('id'));

		if($hashed=="5f1be7240b750d612c22d2273430138d9bb3a1c6d9635645729fcb53c2164668"){
			/**
			 * GIT DEPLOYMENT SCRIPT
			 *
			 * Used for automatically deploying websites via github or bitbucket, more deets here:
			 *
			 *		https://gist.github.com/1809044
			 */

// The commands
			$commands = array(
				'echo $PWD',
				'whoami',
				'git reset --hard origin/master',
				'git pull',
				'git status',
				'git submodule sync',
				'git submodule update',
				'git submodule status',
			);

// Run the commands for output
			$output = '';
			foreach($commands AS $command){
				// Run it
				$tmp = shell_exec($command);
				// Output
				$output .= "<span style=\"color: #6BE234;\">\$</span> <span style=\"color: #729FCF;\">{$command}\n</span>";
				$output .= htmlentities(trim($tmp)) . "\n";
			}

// Make it pretty for manual user access (and why not?)

			echo <<<EOL
<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title>GIT DEPLOYMENT SCRIPT</title>
</head>
<body style="background-color: #000000; color: #FFFFFF; font-weight: bold; padding: 0 10px;">
<pre>
 .  ____  .    ____________________________
 |/      \|   |                            |
[| <span style="color: #FF0000;">&hearts;    &hearts;</span> |]  | Git Deployment Script v0.1 |
 |___==___|  /              &copy; oodavid 2012 |
              |____________________________|
EOL;
			echo $output;
			echo <<<EOL
</pre>
</body>
</html>

EOL;

		}

	}




	/*public function actionResetUserObjectV2()
	{
		$model = new ResetAccountV2('resetUserObject');
		$model->attributes = $_POST;
		if ($model->validate()){
			$model->resetUserObject();
		}else
			echo json_encode($model->getErrors());
	}*/



	public function actionCheckLoginTokenV2()
	{
		$model = new ResetAccountV2('checkLoginToken');
		$model->attributes = $_POST;
		if ($model->validate()){
			$model->checkLoginToken();
		}else
			echo json_encode($model->getErrors());
	}


	public function actionCheckStepsV2()
	{
		$model = new ResetAccountV2('checkStep');
		$model->attributes = $_POST;
		if ($model->validate()){
			$model->checkStep();
		}else
			echo json_encode($model->getErrors());
	}
	public function actionCheckTokenHashesV2()
	{
		$model = new ResetAccountV2('checkTokenHashes');
		$model->attributes = $_POST;
		if ($model->validate()){
			$model->checkTokenHashes();
		}else
			echo json_encode($model->getErrors());
	}


	public function actionChangeLoginPassV2()
	{
		$model = new ResetAccountV2('changeLoginPass');
		$model->attributes = $_POST;
		if ($model->validate()){
			$model->changeLoginPass();
		}else
			echo json_encode($model->getErrors());
	}




	public function actionGetRawUserDataV2()
	{
		$model = new GetUserDataV2();
		$model->attributes = $_POST;

		if ($model->validate()){
			$model->getRawUserObjects(Yii::app()->user->getId());
		}else
			echo json_encode($model->getErrors());
	}

	public function actionUpdateUserDataV2()
	{
		$model = new UpdateUserDataV2('updateV2');
		$model->attributes = $_POST;

		if ($model->validate()){
			$model->updateV2(Yii::app()->user->getId());
		}else
			echo json_encode($model->getErrors());
	}

	public function actionGetUserObjCheckSumV2()
	{
		$model = new GetUserObjCheckSumV2('user');
		$model->attributes = $_POST;

		if ($model->validate()){
			$model->getUserCheckSum(Yii::app()->user->getId());
		}else
			echo json_encode($model->getErrors());
	}

	public function actionGetObjByIndexV2()
	{
		$model = new GetUserObjectsV2('objByIndex');
		$model->attributes = $_POST;

		if ($model->validate()){
			$model->getObjByIndex(Yii::app()->user->getId());
		}else
			echo json_encode($model->getErrors());
	}

	public function actionUpdateObjectsV2_obsolete()
	{
		$model = new SaveUserInputsV2('updateAllUsersObj');
		$model->attributes = $_POST;

		if ($model->validate()){
			$model->savingObjects(Yii::app()->user->getId());
		}else
			echo json_encode($model->getErrors());
	}



	public function actionCleanUp()
	{

	//	public function findAll($collectionName,$data,$selectFields=array(),$limit=null)

		if($ref=Yii::app()->mongo->rrt('mailQueue',array(),array(),10)){

			$dateTime = new DateTime('@'.$ref[0]['_id']->getTimestamp());
			$dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));

			//print_r($dateTime);
			//echo '
			//	';

			foreach($ref as $ind=>$rf){

				if(isset($rf['file'])){
					$files[(string)$rf['_id']]=json_decode($rf['file']);
				}else{
					$messages[]=(string)$rf['_id'];
				}
			}

			if(isset($files)){
				//print_r($files);
				$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
				$host = Yii::app()->params['host'];
				$folder=Yii::app()->params['folder'];
				$username = Yii::app()->params['username'];
				$password = Yii::app()->params['password'];
				$objectStorage = new ObjectStorage($host, $username, $password, $options);

				foreach($files as $messageId=>$fileArray){
					$mngData[]=array('_id'=>new MongoId($messageId));

					foreach($fileArray as $i=>$fName){
						$fOname=hash('sha512',$fName);
						$res = $objectStorage->with($folder.'/'.$fOname)->delete();
						echo 'del file: ';print_r($res);
						echo '----
						';
					}
				}
				$mngDataAgregate=array('$or'=>$mngData);
				Yii::app()->mongo->removeAll('mailQueue',$mngDataAgregate);
				unset($mngData,$fOname,$res,$mngDataAgregate);
			}
			if(isset($messages)){
				//print_r($messages);
				foreach($messages as $ind=>$messageId){
					$mngData[]=array('_id'=>new MongoId($messageId));
				}
				$mngDataAgregate=array('$or'=>$mngData);
				Yii::app()->mongo->removeAll('mailQueue',$mngDataAgregate);
			}


		}
		//print_r($ref);

		echo 'ddd';

	}


	public function actionCheckEmailExistV2()
	{
		$model = new CheckIfExistV2('email');

				$model->attributes = $_POST;
			if ($model->validate()) //validating json data according to action
				$model->validateEmail(Yii::app()->user->getId());
			else
				echo json_encode($model->getErrors());


	}
	public function actionCheckIfPasswordOkV2()
	{
		$model = new CheckIfExistV2('password');

		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->validatePassword(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());


	}

	public function actionSetup2FacV2()
	{
		if($_POST['fac2Type']=="google"){

			$model = new Google2FactV2('verifySecret');

			$model->attributes = $_POST;
			if ($model->validate()) //validating json data according to action
				$model->verifyCode(Yii::app()->user->getId());
			else
				echo json_encode($model->getErrors());


		}else if($_POST['fac2Type']=="yubi"){

			$model = new YubiKey2FactV2('verifySecret');

			$model->attributes = $_POST;
			if ($model->validate()) //validating json data according to action
				$model->verifyCode(Yii::app()->user->getId());
			else
				echo json_encode($model->getErrors());

		}

	}

	public function actionCheckDomainExistV2()
	{
		$model = new CheckIfExistV2('domain');

		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->validateDomain(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());

	}


	public function actionRetCustomDomainUserV2()
	{
		$model = new CustomDomainV2('retrieveDomainsForUser');

		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->retrieveDomainsForUser(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());

	}

	public function actionCheckKeyUniqueV2()
	{
		$model = new CheckIfExistV2('publicKey');

		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->ifKeyisUniq();
		else
			echo json_encode($model->getErrors());

	}

	public function actionRetrievePlanPricingV2()
	{
		$model = new PlansWorkerV2('retrievePrice');

		//$model->attributes = $_POST;
		//if ($model->validate()) //validating json data according to action
			$model->retrievePrice();
		//else
		//	echo json_encode($model->getErrors());

	}

	public function actionCreateOrderBitcoinV2()
	{
		$model = new paymentApiV2('bitcoinCreateOrder');

		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->bitcoinCreateOrder();
		else
			echo json_encode($model->getErrors());

	}



	public function actionCalculatePriceV2()
	{
		$model = new paymentApiV2('calculatePrice');

		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->calculatePrice();
		else
			echo json_encode($model->getErrors());

	}

	public function actionRetrieveUserPlanV2()
	{
		$model = new PlansWorkerV2('retrievePrice');

		//$model->attributes = $_POST;
		//if ($model->validate()) //validating json data according to action
		$model->retrieveUserPlan(Yii::app()->user->getId());
		//else
		//	echo json_encode($model->getErrors());

	}

	public function actionUpdateSecretTokenV2()
	{
		$model = new UpdateUserDataV2('updateToken');

		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->updateToken(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());


		//todo reset pass for 1 step
		//todo reset secret for second pass
		
	}



	public function actionAssignTypesV2()
	{
		$addresses=$_POST;
		$userId=Yii::app()->user->getId();

		$doNeed=Yii::app()->mongo->findById('user',Yii::app()->user->getId(),array('needUpdate'=>1));

		//print_r($doNeed['needUpdate']);
		if(!isset($doNeed['needUpdate'])){


		foreach($addresses as $email=>$type){

			$addressObj=array(
				"addr_type"=>(int)$type
			);

			$criteria=array("userId" =>$userId,"addressHash"=>$email);
			$ff=Yii::app()->mongo->update('addresses',$addressObj,$criteria);

			unset($addressObj,$criteria);
		}


			$userObj = array(
				"needUpdate" =>1,
			);

			$criteria = array("_id" => new MongoId($userId));

			$user = Yii::app()->mongo->update('user', $userObj, $criteria);
	}

		//print_r();
	}

	public function actionClaimFreeV2()
	{
		$model = new PlansWorkerV2('claimFree');

		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->claimFree(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());

	}



	public function actionSavePlanV2()
	{
		$model = new PlansWorkerV2('savePlan');

		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->savePlan(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());

	}

	public function actionCallBackBitcoinV2()
	{
		//

		$hashed=hash('sha256',Yii::app()->getRequest()->getQuery('id'));

		if($hashed=="63ea81dec281ece06f823eede0fc680008d13ae0fa16f083cf60880456c8101a"){
			$model = new CallBacksV2('bitcoin');
			//$model->attributes = $_POST;
			//if ($model->validate()) //validating json data according to action
			$model->bitcoin(Yii::app()->user->getId());
		}

	}

	public function actionCallBackPayPalV2()
	{
		//confirmPaypal

		$hashed=hash('sha256',Yii::app()->getRequest()->getQuery('id'));

		if($hashed=="ae20a0b6d4a4df06459acb5e15a347daa7bd6e01ecd3bbb0a66cc8eb815994f7"){
			$model = new CallBacksV2('paypal');
			//$model->attributes = $_POST;
			//if ($model->validate()) //validating json data according to action
			$model->paypal();
		}

	}

	public function actionRetrieveFoldersMetaTempV2()
	{
		//temporary for migration, remove after migration period is over

		$model = new RetrieveFoldersMetaTemp('importingData');
		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->getMeta();
		else
			echo json_encode($model->getErrors());

	}

	public function actionRetrieveMessageV2()
	{
		$model = new RetrieveMessageV2('retrieveRegisteredEmail');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->show();
		else
			echo json_encode($model->getErrors());
	}


	public function actionGetTrustedSendersV2()
	{
		$result['response']='success';

		foreach(Yii::app()->params['trustedSenders'] as $row){
			$result['data']['senders'][]=hash('sha256',$row);
		}

		echo json_encode($result);

		//$model = new RetrieveMessageV2();
		//$model->attributes = isset($_POST) ? $_POST : '';
		//if ($model->validate())
		//	$model->show();
		//else
		//	echo json_encode($model->getErrors());
	}
	public function actionGetPublicKeysV2()
	{
		$model = new GetPublicKeysV2();
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->getKeys();
		else
			echo json_encode($model->getErrors());
	}

	public function actionEmailsOwnershipsV2()
	{
		$model = new GetPublicKeysV2();
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->ifWeOwn();
		else
			echo json_encode($model->getErrors());
	}

	public function actionGetDraftMessageIdV2()
	{
		$model = new EmailWorkerV2('generateMessageId');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->generateId();
		else
			echo json_encode($model->getErrors());
	}

	public function actionSavingUserObjectsV2()
	{
		$model = new SavingUserDataV2('updatingObjects');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->saveObjects(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}

	public function actionSaveDraftEmailV2()
	{
		$model = new SavingUserDataV2('saveDraftEmail');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->saveDraftEmail(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}


	/**
	 * generaing new PGP keys for existing address
	 */
	public function actionSavingUserObjWnewPGPkeysV2()
	{
		$model = new SavingUserDataV2('savingUserObjWnewPGPkeys');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->savingUserObjWnewPGPkeys(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}

	public function actionSendEmailClearTextV2()
	{
		$model = new SavingUserDataV2('sendEmailClearText');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->sendEmailClearText(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}

	public function actionSaveNewEmailOldV2()
	{
		$model = new SavingUserDataV2('saveNewEmailOld');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->saveNewEmailOld(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}
	public function actionSaveNewEmailV2()
	{
		$model = new SavingUserDataV2('saveNewEmailV2');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->saveNewEmailV2(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}

	public function actionDeleteEmailV2()
	{
		$model = new SavingUserDataV2('deleteEmailV2');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->deleteEmailV2(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}




	public function actionDeleteEmailUnregV2()
	{
		$model = new DeleteEmailV2('deleteEmailUnreg');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->deleteEmailUnreg(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}



	public function actionSendEmailWithPinV2()
	{
		$model = new SavingUserDataV2('sendEmailWithPin');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->sendEmailWithPin(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}

	public function actionSendEmailPGPV2()
	{
		$model = new SavingUserDataV2('sendEmailPGP');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->sendEmailPGP(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}

	public function actionSendEmailIntV2()
	{
		$model = new SavingUserDataV2('sendEmailInt');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->sendEmailInt(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}

	public function actionSendEmailUnregV2
	()
	{
		$model = new SendEmailUnregV2('sendEmailUnreg');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->sendEmailUnreg(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}


	/**
	 * Creating new disposable or alias email address
	 */

	public function actionSavingUserObjWnewPGPV2()
	{
		$model = new SavingUserDataV2('savingUserObjWnewPGP');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->savingUserObjWnewPGP(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}

	public function actionSavingUserObjWdeletePGPV2()
	{
		$model = new SavingUserDataV2('savingUserObjWdeletePGP');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->savingUserObjWdeletePGP(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}


	public function actionUpdateDomainV2()
	{
		$model = new SavingUserDataV2('updateDomain');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->updateDomain(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}


	public function actionSavePendingDomainV2()
	{
		$model = new SavingUserDataV2('savePendingDomain');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->savePendingDomain(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());

	}

	public function actionDeleteDomainV2()
	{
		$model = new SavingUserDataV2('deleteDomain');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->deleteDomain(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());

	}

	public function actionFolderSettingsV2()
	{
		$model = new SavingUserDataV2('folderSettings');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->folderSettings(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());

	}





	public function actionChangePassV2()
	{
		//if(Yii::app()->request->isAjaxRequest){

			$model = new ChangePassV2('changeLoginPass');
			$model->attributes = $_POST;
			if ($model->validate()) //validating json data according to action
				$model->changeLoginPass(Yii::app()->user->getId());
			else
				echo json_encode($model->getErrors());
		//}
	}



	public function actionChangePassOneStepV2()
	{
		//if(Yii::app()->request->isAjaxRequest){

		$model = new ChangePassV2('changePassOneStep');
		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->changePassOneStep(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
		//}
	}



	public function actionChangeSecondPassV2()
	{
		//if(Yii::app()->request->isAjaxRequest){

			$model = new ChangePassV2('changeSecondPass');
			$model->attributes = $_POST;
			if ($model->validate()) //validating json data according to action
				$model->changeSecondPass(Yii::app()->user->getId());
			else
				echo json_encode($model->getErrors());
		//}
	}

	public function actionSaveGoogleAuthV2()
	{
		//if(Yii::app()->request->isAjaxRequest){

			$model = new ChangePassV2('saveGoogleAuth');
			$model->attributes = $_POST;
			if ($model->validate()) //validating json data according to action
				$model->saveGoogleAuth(Yii::app()->user->getId());
			else
				echo json_encode($model->getErrors());
		//}
	}

	public function actionAvailableDomainsForAliasV2()
	{
		//if(Yii::app()->request->isAjaxRequest){

			$model = new DomainsV2('availableDomainsForAlias');
			$model->attributes = $_POST;
			if ($model->validate()) //validating json data according to action
				$model->availableDomainsForAlias(Yii::app()->user->getId());
			else
				echo json_encode($model->getErrors());
		//}
	}

	public function actionDeleteUserV2()
	{
		//if(Yii::app()->request->isAjaxRequest){

			$model = new DeleteAccountV2();
			$model->attributes = $_POST;
			if ($model->validate()) //validating json data according to action
				$model->removeAccount(Yii::app()->user->getId());
			else
				echo json_encode($model->getErrors());
		//}
	}

	public function actionRetrievePublicKeysV2()
	{
		//if(Yii::app()->request->isAjaxRequest){

		$model = new RetrievePublicKeysV2('retrieveKey');
		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->retrieveKey();
		else
			echo json_encode($model->getErrors());

		//}
	}

	public function actionRetrievePublicKeyUnregV2()
	{
		//if(Yii::app()->request->isAjaxRequest){

		$model = new RetrievePublicKeysV2('retrievePublicKeyUnreg');
		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->retrievePublicKeyUnreg();
		else
			echo json_encode($model->getErrors());

		//}
	}



	public function actionSaveNewAttachmentV2()
	{
		//if(Yii::app()->request->isAjaxRequest){

		$model = new FileWorkerV2('saveNewAttachment');
		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->saveNewAttachment(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());

		//}
	}

	public function actionRemoveFileFromDraftV2()
	{
		//if(Yii::app()->request->isAjaxRequest){

		$model = new FileWorkerV2('removeFileFromDraft');
		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->removeFileFromDraft(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());

		//}
	}


	public function actionDownloadFileUnregV2()
	{

		$model = new FileWorkerV2('downloadFileUnreg');

		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->downloadFileUnreg(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());

	}


	public function actionDownloadFileV2()
	{

		$model = new FileWorkerV2('downloadFile');

		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->downloadFile(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());

	}


	public function actionDFV2()
	{

		$model = new FileWorkerV2('downloadFilePublic');

		$model->fileId=Yii::app()->getRequest()->getQuery('id');
		$model->filePass= Yii::app()->getRequest()->getQuery('p');

		//print_r(Yii::app()->getRequest()->getQuery('id'));
		//print_r(Yii::app()->getRequest()->getQuery('p'));
		//print_r(Yii::app()->getRequest()->getQuery('fileName'));

		if($model->validate()){
			$model->downloadPublic();
		}else
			echo json_encode($model->getErrors());

	}

	public function actionDeleteFileFromSafe()
	{
		$model = new SafeBox('deleteFileFromSafe');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->deleteFileFromSafe(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}

	public function actionGetSafeBoxList()
	{
		$model = new SafeBox('retrieveList');
		$model->attributes = isset($_POST) ? $_POST : '';
		if ($model->validate())
			$model->retrieveList(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}

	public function actionSafeBox()
	{
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			header("WWW-Authenticate: Basic realm=\"Private Area\"");
			header("HTTP/1.0 401 Unauthorized");
			exit;
		} else {

			if(isset($_SERVER['HTTP_AUTHORIZATION'])){
				try{

					$stringData = base64_decode(trim(str_replace('Basic','',$_SERVER['HTTP_AUTHORIZATION'])));
					$userpass=explode(':',$stringData);
					$user=$userpass[0];
					$pass=$userpass[1];

					$model = new SafeBox('safeFile');
					$model->file=file_get_contents("php://input");
					$model->filename=Yii::app()->getRequest()->getQuery('fileName');

					//$model->username=isset($_SERVER['HTTP_AUTHORIZATION'])?$_SERVER['HTTP_AUTHORIZATION']:'';

					$model->username=isset($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:$userpass[0];
					$model->password=isset($_SERVER['PHP_AUTH_PW'])?$_SERVER['PHP_AUTH_PW']:$userpass[1];
					$model->action=isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'';

					if(strlen($model->filename)!=0)
					{
						if ($model->validate())
							$model->fileWorks();

					}else
					{
						header($_SERVER['SERVER_PROTOCOL'] . ' 400'.$_SERVER['PHP_AUTH_USER'].$_SERVER['PHP_AUTH_PW'], true, 400);
						echo ' ';

					}


				} catch (Exception $e) {
					header($_SERVER['SERVER_PROTOCOL'] . ' 400'.$_SERVER['PHP_AUTH_USER'].$_SERVER['PHP_AUTH_PW'], true, 400);
					echo ' ';
				}


			}else{
				header($_SERVER['SERVER_PROTOCOL'] . ' 400'.$_SERVER['PHP_AUTH_USER'].$_SERVER['PHP_AUTH_PW'], true, 400);
				echo ' ';
			}


		}


	}



	public function actionGetNewSeedsV2()
	{

		$model = new GetNewSeedV2('getData');

		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->getData(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}

	public function actionGetNewMetaV2()
	{

		$model = new GetNewSeedV2('getNewMeta');

		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->getNewMeta(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}


	public function actionRetrieveUnregEmailV2()
	{

		$model = new RetrieveMessageV2('retrieveUnregEmail');

		$model->attributes = $_POST;
		if ($model->validate()) //validating json data according to action
			$model->retrieveUnregEmail(Yii::app()->user->getId());
		else
			echo json_encode($model->getErrors());
	}










	//==========================
	//old block
	public function actionCheckMongo()
	{

		echo 'Testing MySql: ';
		try {
			Yii::app()->db->createCommand("SELECT mailKey,v FROM addresses LIMIT 1")->queryRow();
			echo 'OK
			<br>';

		} catch (Exception $e) {
			echo 'Fail
			<br/>';
		}



		echo 'Testing Mongo: ';
		try {
		if ($allUser = Yii::app()->mongo->findAll('user',array(),array('_id'=>1))) {
			echo 'OK
			<br>';
		}
		} catch (Exception $e) {
			echo 'Fail
			<br/>';
		}

		echo 'Memory: Generating 20MB File: ';

		try {
			$size = 1024 * 1024 * 20;
			$ff= str_pad('', $size,'b');
			$size = strlen($ff);

		echo 'Success
		<br/>';

		} catch (Exception $e) {
			echo 'Failed
		<br/>';
		}

		$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
		$host = Yii::app()->params['host'];
		$folder=Yii::app()->params['folder'];
		$username = Yii::app()->params['username'];
		$password = Yii::app()->params['password'];
		$objectStorage = new ObjectStorage($host, $username, $password, $options);

		echo 'Object Storage: Saving 20MB file: ';

		try {
			$obWrite=$objectStorage->with('atach_debug_local/testing.txt')
				->setBody($ff)
				->setHeader('Content-type', 'application/octet-stream')
				->create();
			echo 'Success
		<br/>';
			print_r($obWrite);

		} catch (Exception $e) {
			echo 'Failed
		<br/>';
		}


		print_r($size);


		/*echo '--Testing Object Storage--
		<br/>
		';

		$options = array('adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 10);
		$host = Yii::app()->params['host'];
		$folder=Yii::app()->params['folder'];
		$username = Yii::app()->params['username'];
		$password = Yii::app()->params['password'];

		$objectStorage = new ObjectStorage($host, $username, $password, $options);

		$object = $objectStorage->with('atach_debug_local/2a40ba9d861a60c4a04a1a77c6243698ae6035aecf0b4046bc2fbb815427b4ce')->get();



		//print_r(substr($object->getBody(),0,1000));

		$ff=$objectStorage->with('atach_debug_local/testing.txt')
			->setBody(substr($object->getBody(),0,1800000))
			->setHeader('Content-type', 'application/octet-stream')
			->create();

		//print_r($ff);

		print_r('sdfsdf');*/

	}





	public function actionSubmitBug()
	{

		$model = new SubmitBug();
		if(isset($_POST['email'])){
			$model->attributes =$_POST;
			if($model->name==""){
				if ($model->validate()){ //validating json data according to action
					$model->sendBug();
				}
			}else{
				$res['answer']='Please fill all fields manually.';
				echo json_encode($res);
				}
		}else{
			$this->redirect('/login#submitBug');
		}
	}

	public function actionSubmitError(){
		$model = new SubmitError();


		if(isset($_POST['errorObj'])){
			$model->attributes =$_POST;
				if ($model->validate()){ //validating json data according to action
					$model->sendErrorReport();
				}


		}

	}


	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if ($error = Yii::app()->errorHandler->error) {
			if (Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}


	public function actionGetClientInfo(){
		$data['referer']=isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
		$data['ip']=$_SERVER['REMOTE_ADDR'];
		$data['agent']=$_SERVER['HTTP_USER_AGENT'];
		$data['https']=$_SERVER['HTTPS'];
		$data['geoIP']=geoip_record_by_name('209.85.223.182');
		$data['browser']=get_browser(null, true);

		echo json_encode($data);
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}
