<?php

/**
 * Sergei Krutov
 * Date: 12/28/15
 * Time: 1:12 PM
 */
class SendEmailUnregV2 extends CFormModel
{
	public $emailData,$refId;


	public function rules()
	{
		return array(

			//sendEmailIntV2

			array('emailData', 'match', 'pattern' => "/^[a-zA-Z0-9+{\[\]:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'sendEmailUnreg','message'=>'fld2upd1'),
			array('emailData','length', 'max'=>3000000,'allowEmpty' => true,'on'=>'sendEmailUnreg','message'=>'fld2upd2'),

			array('refId', 'match', 'pattern' => "/^[a-z0-9\d]{24}$/i", 'allowEmpty' => false, 'on' => 'sendEmailUnreg','message'=>'fld2upd3'),

			array('emailData', 'checkDatasInt', 'on' => 'sendEmailUnreg'),

		);
	}



	public function extract_email_address ($string) {
		$emails=array();
		foreach(preg_split('/\s/', $string) as $token) {
			$email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
			if ($email !== false) {
				$emails[] = $email;
			}
		}
		return $emails;
	}


	public function checkDatasInt(){

		if($encryptedEmail=json_decode($this->emailData,true)){

		//	print_r($encryptedEmail);

			$totalRecipients=0;
			$totalRecipients+=isset($encryptedEmail['toCCrcpt']['recipients'])?count($encryptedEmail['toCCrcpt']['recipients']):0;
			$totalRecipients+=count($encryptedEmail['bccRcpt']);
			$totalRecipients+=count($encryptedEmail['bccRcptV1']);
			$totalRecipients+=count($encryptedEmail['toCCrcptV1']);
			//$this->totalRecipients=$totalRecipients;

			if($totalRecipients>1){
				$this->addError('Rcpnt', 'should be one');
			}


			if(isset($encryptedEmail['toCCrcpt']['recipients']) && count($encryptedEmail['toCCrcpt']['recipients'])==1){

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

			if(!empty($encryptedEmail['toCCrcptV1'])){

				$this->addError('toCCrcptV1:seedRcpnt', 'should be empty');
			}

			if(!empty($encryptedEmail['bccRcptV1'])){

				$this->addError('bccRcptV1:seedRcpnt', 'should be empty');
			}


			if(!empty($encryptedEmail['bccRcpt'])){
				$this->addError('bccRcpt:seedRcpnt', 'should be empty');
			}

			if(!empty($encryptedEmail['attachments'])){
				$this->addError('attachments', 'should be empty');
			}

			if(!ctype_xdigit($encryptedEmail['modKey']) || strlen($encryptedEmail['modKey'])!==128){
				$this->addError('modKey', 'notValid');
			}


		}else{
			$this->addError('emailData', 'notJson');
		}
		//$this->addError('emailData', 'notJson');
	}

	public function sendEmailUnreg($userId){

		$result['response']='success';
		$result['data']='fail';
		$encryptedEmail=json_decode($this->emailData,true);

		$encryptedEmail['aSize']=0;

		$param[':email']=json_encode($encryptedEmail);
		//$param[':pKey']=$encryptedEmail['mailKey'];

		$param[':modKey']=$encryptedEmail['modKey'];
		$param[':refId']=$this->refId;
		$param[':destination']=4;


		$trans = Yii::app()->db->beginTransaction();

		if(Yii::app()->db->createCommand('INSERT INTO mail2sent (email,refId,modKey,destination) VALUES(:email,:refId,:modKey,:destination)')->execute($param))
		{

				$result['response']='success';
				$result['data']='saved';
				$trans->commit();

		}



		echo json_encode($result);

	}

}