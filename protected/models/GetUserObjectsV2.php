<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class GetUserObjectsV2 extends CFormModel
{

	public $userToken;
	public $objIndex,$obj;


	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),
			array('objIndex', 'numerical','integerOnly'=>true,'allowEmpty'=>false, 'on' => 'objByIndex','message'=>'updIndWrong'),

			array('obj', 'match', 'pattern'=>'/^([a-zA-Z])+$/','message'=>'fldObj','on'=>'objByIndex'),
			array('obj','length', 'min' => 3, 'max'=>40,'tooShort'=>'fldObj','tooLong'=>'fldObj','on'=>'objByIndex'),
			//userByIndex
		);
	}
	public function chkToken(){
		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}


	public function getObjByIndex($userId)
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


		//todo remove when migration is over
		if(Yii::app()->user->getVersion()==1)
		{

			if($userEncoded=Yii::app()->db->createCommand("SELECT $colName FROM user WHERE id=$userId")->queryScalar())
			{

				$userDec=json_decode($userEncoded,true);

				if($userDec[$this->objIndex]['index']==$this->objIndex){
					$result['response']="success";
					$result['data']=$userDec[$this->objIndex];
				}else{
					foreach($userDec as $row){
						if($row['index']==$this->objIndex){
							$result['response']="success";
							$result['data']=$userDec[$this->objIndex];
						}
					}
				}

				echo json_encode($result);

			}else {
				echo '{"response":"fail"}';
			}

		}else if(Yii::app()->user->getVersion()==2) {


			if ($objects = Yii::app()->mongo->findByUserIdNew('userObjects', $userId, array($colName => 1))) {

				$userDec = json_decode($objects[0][$colName]->bin, true);

				if ($userDec[$this->objIndex]['index'] == $this->objIndex) {
					$result['response'] = "success";
					$result['data'] = $userDec[$this->objIndex];
				} else {
					foreach ($userDec as $row) {
						if ($row['index'] == $this->objIndex) {
							$result['response'] = "success";
							$result['data'] = $userDec[$this->objIndex];
						}
					}
				}

				echo json_encode($result);

			} else {
				echo '{"response":"fail"}';
			}

		}



	}


}