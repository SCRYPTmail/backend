<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */
class StatsV2 extends CFormModel
{

	public $userToken;
	public function rules()
	{
		return array(
			//array('userToken', 'chkToken'),
			// username and password are required
			//array('domain', 'required','on'=>'validateDomain'),
			//array('domain', 'match', 'pattern'=>'/^([a-z0-9_])+$/', 'message'=>'please provide correct domain','on'=>'validateDomain'),
			//array('domain','length', 'min' => 128, 'max'=>128, 'tooShort'=>'domain not found','tooLong'=>'domain not found','on'=>'validateDomain'),
			//array('domains', 'required','on'=>'checkDomain'),
		);
	}

	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}

	public function counter($type,$amount=1)
	{
		try {
			$today = date("Y-m-d H");

			$userObj = array();

			$incremental = array(
				"counter.$today.count" => $amount,
			);

			$upsert = true;

			$criteria = array("action" => $type);
			Yii::app()->mongo->update('statistic', $userObj, $criteria, $incremental, $upsert);


		}catch (Exception $e) {

		}
	}



	public function availableDomainsForAlias($userId)
	{
		//display domains people can create alias

		$result['response']='fail';

		$param[':userId']=$userId;
		if($domains=Yii::app()->db->createCommand("SELECT domain FROM virtual_domains WHERE (userId=:userId AND availableForAliasReg=1) OR globalDomain=1")->queryAll(true,$param)){
			$result['response'] = 'success';
			$result['data']=$domains;
		}

		echo json_encode($result);
	}

}