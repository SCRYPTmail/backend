<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class EmailWorkerV2 extends CFormModel
{

	public $userToken;
	public $modKey;

	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),
			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32}$/i", 'allowEmpty' => false, 'on' => 'generateMessageId','message'=>'fld2upd'),

		);
	}

	public function chkToken(){
		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}





	public function generateId()
	{
		//print_r($this->modKey);

		$person[]=array(
			"expireAfter" => new MongoDate(),	//DB have expiration for 1 hour
			"userId"=>Yii::app()->user->getId(),
			"modKey"=>hash('sha512',$this->modKey)
		);

		$result['response']="fail";

		if($message=Yii::app()->mongo->insert('personalFolders',$person))
		{
			$result['data']['messageId']=$message[0];

			$result['response']="success";
			echo json_encode($result);
		}
	}

}