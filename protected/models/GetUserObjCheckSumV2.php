<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class GetUserObjCheckSumV2 extends CFormModel
{

	public $userToken;

	public $obj;
	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),

			array('obj', 'match', 'pattern'=>'/^([a-zA-Z])+$/','message'=>'fldObj','on'=>'user'),
			array('obj','length', 'min' => 3, 'max'=>40,'tooShort'=>'fldObj','tooLong'=>'fldObj','on'=>'user'),
		);
	}
	public function chkToken(){
		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}


	public function getUserCheckSum($userId)
	{
		if($this->obj=="userObj"){
			$colName="userObj";
		}
		if($this->obj=="profObj"){
			$colName="profileSettings";
		}
		if($this->obj=="foldObj"){
			$colName="folderObj";
		}
		if($this->obj=="contObj"){
			$colName="contacts";
		}
		if($this->obj=="spamObj"){
			$colName="blackList";
		}

		//todo remove after migration over
		if(Yii::app()->user->getVersion()==1){

			if($userEncoded=Yii::app()->db->createCommand("SELECT $colName FROM user WHERE id=$userId")->queryScalar()){

				$userDec=json_decode($userEncoded,true);
				foreach($userDec as $i=>$row){
					unset($userDec[$i]['data']);
				}
				$result['response']="success";
				$result['data']=$userDec;

				echo json_encode($result);
			}else
				echo '{"response":"fail"}';


		}else if(Yii::app()->user->getVersion()==2){
			$objects = Yii::app()->mongo->findByUserIdNew('userObjects', $userId, array($colName => 1));

			$userDec=json_decode($objects[0][$colName]->bin,true);
			foreach($userDec as $i=>$row){
				unset($userDec[$i]['data']);
			}
			$result['response']="success";
			$result['data']=$userDec;
		}


		echo json_encode($result);



	}


}