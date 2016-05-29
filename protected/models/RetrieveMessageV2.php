<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class RetrieveMessageV2 extends CFormModel
{

	public $messageId,$modKey,$userToken;

	public $emailId,$pin,$pinOld;



	public function rules()
	{
		return array(
			array('userToken', 'chkToken','on'=>'retrieveRegisteredEmail'),
			// username and password are required
			//array('messageIds','checkArray'),
			//array('messageId', 'numerical', 'integerOnly' => true, 'allowEmpty' => false),
			array('messageId', 'match', 'pattern'=>'/^([a-z0-9 _])+$/', 'message'=>'messageId is not correct','on'=>'retrieveRegisteredEmail'),
			array('messageId','length', 'min' => 1, 'max'=>24,'on'=>'retrieveRegisteredEmail'),

			array('modKey', 'match', 'pattern'=>'/^([a-z0-9 _])+$/', 'message'=>'modKey is not correct','on'=>'retrieveRegisteredEmail'),
			array('modKey','length', 'min' => 32, 'max'=>32,'on'=>'retrieveRegisteredEmail'),

			//retrieveUnregEmail
			array('emailId', 'match', 'pattern'=>'/^([a-z0-9 _])+$/', 'message'=>'emailId is not correct','on'=>'retrieveUnregEmail'),
			array('emailId','length', 'min' => 24, 'max'=>128,'on'=>'retrieveUnregEmail'),

			array('pin,pinOld', 'match', 'pattern'=>'/^([a-z0-9 _])+$/', 'message'=>'pin is not correct','on'=>'retrieveUnregEmail'),
			array('pin','length', 'min' => 64, 'max'=>64,'on'=>'retrieveUnregEmail'),
			array('pinOld','length', 'min' => 128, 'max'=>128,'on'=>'retrieveUnregEmail'),

		);
	}


	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}



	public function retrieveUnregEmail()
	{
		$result['response']='fail';
		$result['data']=-1;
		//pinOld

		if(ctype_xdigit($this->emailId)){

			//old version with long id
			if(strlen($this->emailId)===128){
				$newMail2field=array('oldId'=>$this->emailId);

				if($newMails=Yii::app()->mongo->findOne('mailQueue',$newMail2field)){

					if($newMails['pinHash']==$this->pinOld && $newMails['tryCounter']<=2){

						$criteria=array("_id" => new MongoId($newMails['_id']));
						$person=array("tryCounter" => 0);
						$message=Yii::app()->mongo->update('mailQueue',$person,$criteria);

						$result['response']='success';
						$result['data']=array(
							'email'=>array('body'=>bin2hex($newMails['body']->bin),'meta'=>bin2hex($newMails['meta']->bin)),
							'messageId'=>$this->emailId,
							'version'=>1
						);

					}

					else if($newMails['pinHash']!=$this->pinOld && $newMails['tryCounter']<=3){
						//if pin is invalid add counter

						$criteria=array("_id" => new MongoId($newMails['_id']));

						if($newMails['tryCounter']<2){
							$person=array("tryCounter" => $newMails['tryCounter']+1);
							$message=Yii::app()->mongo->update('mailQueue',$person,$criteria);
							$result['response']='fail';
							$result['data']=$newMails['tryCounter']+1;
							//echo '{"emailId":["Emailhash Not Found"]}';
						}

						//set expire immediately for crawler cleanup
						if($newMails['tryCounter']>=2){
							$person=array(
								"tryCounter" => $newMails['tryCounter']+1,
								"expireAfter"=>new MongoDate(strtotime('now'))
							);
							$message=Yii::app()->mongo->update('mailQueue',$person,$criteria);
							$result['response']='fail';
							$result['data']=$newMails['tryCounter']+1;
						}

					}


				}


			}else if(strlen($this->emailId)===24){


				$newMail2field=array('_id'=>new MongoId($this->emailId));

				if($newMails=Yii::app()->mongo->findById('mailQv2',$this->emailId)){
					//print_r($newMails);

					if($newMails['pinHash']==$this->pin && $newMails['tryCounter']<=2){

						$criteria=array("_id" => new MongoId($newMails['_id']));
						$person=array("tryCounter" => 0);
						$message=Yii::app()->mongo->update('mailQueue',$person,$criteria);

						$result['response']='success';
						$result['data']=array(
							'email'=>$newMails['body'],
							'messageId'=>$this->emailId,
							'recipientHash'=>$newMails['recipientHash'],
							'version'=>2
						);

					}

					else if($newMails['pinHash']!=$this->pin && $newMails['tryCounter']<=3){
						//if pin is invalid add counter

						$criteria=array("_id" => new MongoId($newMails['_id']));

						if($newMails['tryCounter']<2){
							$person=array(
								"tryCounter" => $newMails['tryCounter']+1
							);
							$message=Yii::app()->mongo->update('mailQv2',$person,$criteria);
							$result['response']='fail';
							$result['data']=$newMails['tryCounter']+1;
							//echo '{"emailId":["Emailhash Not Found"]}';
						}

						//set expire immediately for crawler cleanup
						if($newMails['tryCounter']>=2){
							$person=array(
								"tryCounter" => $newMails['tryCounter']+1,
								"expireAfter"=>new MongoDate(strtotime('now'))
							);
							$message=Yii::app()->mongo->update('mailQv2',$person,$criteria);
							$result['response']='fail';
							$result['data']=$newMails['tryCounter']+1;
						}

					}


				}


			}


		}


		//print_r($this->emailId);
		//echo '
		//';
		echo json_encode($result);

		//print_r($result);

	}

	public function show()
	{
		$response['response']="fail";

			if(ctype_xdigit($this->messageId) && strlen($this->messageId)==24){
			$query=	array(
				'_id' => new MongoId($this->messageId),
				'modKey'=>hash('sha512',$this->modKey)
			);


			if($ref=Yii::app()->mongo->findOne('personalFolders',$query,array('_id'=>1,'meta'=>1,'body'=>1,'v'=>1))) {

			if(isset($ref['body'])){
				$result['messageHash'] = $this->messageId;
				if (!isset($ref['v']) || $ref['v'] === 1) {
					//echo 'dfdfdf';
					if (isset($ref['meta'])) {
						$result['meta'] = base64_encode(substr($ref['meta']->bin, 0, 16)) . ';' . base64_encode(substr($ref['meta']->bin, 16));
					}
					$result['body'] = base64_encode(substr($ref['body']->bin, 0, 16)) . ';' . base64_encode(substr($ref['body']->bin, 16));
				} else if ($ref['v'] === 15) {
					//echo 'dfdfd546456f';
					if (isset($ref['meta'])) {
						$result['meta'] = $ref['meta']->bin;
					}
					$result['body'] = $ref['body']->bin;
				} else if ($ref['v'] === 2) {
					//echo 'dfdfd546456f';
					if (isset($ref['meta'])) {
						$result['meta'] = $ref['meta']->bin;
					}
					$result['body'] = $ref['body']->bin;
				}

				$response['response']="success";
				$response['data']=json_encode($result);
			}

			}else{
				$response['response']="success";
				$response['data']=array();
			}


		}else{
			$response['response']="success";
			$response['data']=array();
		}

		echo json_encode($response);

	}


}