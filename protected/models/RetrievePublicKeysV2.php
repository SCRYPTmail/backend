<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class RetrievePublicKeysV2 extends CFormModel
{
	public $userToken,$mails;
	public $emailHash;

	public function rules()
	{
		return array(
			// username and password are required
			array('userToken', 'chkToken', 'except'=>'retrievePublicKeyUnreg'),
			array('mails', 'required', 'on' => 'retrieveKey'),
			//array('mail', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'retrieveKey','message'=>'fld2upd'),

			//retrievePublicKeyUnreg

			array('emailHash', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'retrievePublicKeyUnreg','message'=>'fail'),
		);
	}

	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}


	public function retrievePublicKeyUnreg()
	{
		//$this->emailHash
		//$this->messageId

		//print_r($this->emailHash);

		$result['response']='fail';


		$newMail2field=array('addressHash'=> $this->emailHash,'v'=>2,'active'=>1);

		if($key=Yii::app()->mongo->findOne('addresses',$newMail2field,array('mailKey'=>1))){
				$result['response']='success';
				$result['data'] = $key['mailKey'];

		}
		echo json_encode($result);

	}

	public function retrieveKey()
	{

		$result['response']='success';
		$emailsArray = json_decode($this->mails, true);

		foreach ($emailsArray as $i => $row) {
			$mngData[]=array('addressHash'=>$row);
		}
		//print_r($param);

		$mngDataAgregate=array('$or'=>$mngData);

		$result['data'] = array();

		if($hashes=Yii::app()->mongo->findAll('addresses',$mngDataAgregate,array('addressHash'=>1,'mailKey'=>1,'v'=>1))){

			foreach ($hashes as $row)
			{
				$result['data'][$row['addressHash']] = $row;
				$result['data'][$row['addressHash']]['new']=1;
				//todo update react from version to v
			}
		}


		//end delete
		echo json_encode($result);
	}
}