<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class UpdateUserDataV2 extends CFormModel
{

	public $userToken;

	//for update to Version2
	public $profileObject,
		$folderObject,
		$updateKeys,
		$modKey,
		$profileVersion,
		$userObject,
		$contactObject,
		$blackListObject,
		$plan,$tokenHash,$tokenAesHash;

	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),

			array('folderObject,updateKeys,profileObject,userObject,contactObject,blackListObject', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => false, 'on' => 'updateV2','message'=>'fld2upd'),

			array('profileObject,userObject','length', 'max'=>3000000,'min'=>20,'on'=>'updateV2','message'=>'fld2upd'),

			array('folderObject,contactObject,blackListObject','length', 'max'=>13000000,'min'=>20,'on'=>'updateV2','message'=>'fld2upd'),

			array('profileVersion', 'numerical','integerOnly'=>true,'allowEmpty'=>false, 'on' => 'updateV2','message'=>'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'updateV2','message'=>'fld2upd'),
			array('tokenHash,tokenAesHash', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'updateV2','message'=>'fld2upd'),


			//updateToken
			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'updateToken','message'=>'fld2upd'),
			array('tokenHash,tokenAesHash', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'updateToken','message'=>'fld2upd'),

			array('folderObject,profileObject,userObject,contactObject,blackListObject,plan', 'match', 'pattern' => "/^[a-zA-Z0-9+{:}\",\/=;\d]+$/i", 'allowEmpty' => true, 'on' => 'updateV2','message'=>'fld2save'),


		);
	}

	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}


	public function updateToken($userId)
	{
		$result['response']='fail';

		$userObj=array(
			"tokenHash"=>$this->tokenHash,
			"tokenAesHash"=>$this->tokenAesHash
		);

		$criteria=array("_id" => new MongoId($userId),'modKey'=>hash('sha512',$this->modKey));

		if(Yii::app()->mongo->update('user',$userObj,$criteria)){
			$result['response']='success';
		}

		echo json_encode($result);

	}

	public function updateV2($userId)
	{
		$result['response']='fail';
		$param[':modKey']=hash('sha512',$this->modKey);
		$param[':id']=$userId;

		if($user=Yii::app()->db->createCommand("SELECT * FROM user WHERE id=:id AND modKey=:modKey")->queryRow(true,$param))
		{
			//print_r($user);

			$newPlan=Yii::app()->params['params']['planData'];

			$userNew[]=array(
				"oldId"=>$userId,
				"mailHash"=>$user['mailHash'],
				"password"=>$user['password'],
				"modKey"=>$user['modKey'],

				"created"=>new MongoDate(strtotime($user['created'])),
				"saltS"=>$user['saltS'],
				"tokenHash"=>$this->tokenHash,
				"tokenAesHash"=>$this->tokenAesHash,
				"active"=>new MongoDate(strtotime($user['active'])),
				"oneStep"=>$user['oneStep'],
				"version"=>2,
				"authSecret"=>$user['authSecret'],
				"2ndType"=>$user['2ndType'],

				'cycleStart'=>new MongoDate(strtotime('now')),
				'cycleEnd'=>new MongoDate(strtotime('now' . '+ 2 month')),
				'balance'=>0,
				'alrdPaid'=>0,
				'pastDue'=>0,
				'monthlyCharge'=>0,
				'creditUsed'=>false,
				'planData'=>json_encode($newPlan)
			);
			$result['response']='success';


			if($message=Yii::app()->mongo->insert('user',$userNew))
			{
				$newUSerId=$message[0];
				$userObj[]=array(
					"userId"=>$newUSerId,
					"profileSettings"=>new MongoBinData($this->profileObject, MongoBinData::GENERIC),
					"folderObj"=>new MongoBinData($this->folderObject, MongoBinData::GENERIC),
					"userObj"=>new MongoBinData($this->userObject, MongoBinData::GENERIC),
					"contacts"=>new MongoBinData($this->contactObject, MongoBinData::GENERIC),
					"blackList"=>new MongoBinData($this->blackListObject, MongoBinData::GENERIC),
					"modKey"=>$user['modKey']
				);

				if($userObjSaved=Yii::app()->mongo->insert('userObjects',$userObj))
				{

						$keys=json_decode($this->updateKeys,true);

					$SavingUserDataV2 = new SavingUserDataV2();

						foreach($keys as $email=>$pKey)
						{

							$em = strtolower($SavingUserDataV2->extract_email_address(base64_decode($email))[0]);
							$dom = hash('sha512', explode('@', $em)[1]);

							//check if user had this account and domain is valid
							if ($verifiedDomains = Yii::app()->db->createCommand(
								"SELECT id,domain,shaDomain FROM virtual_domains
								WHERE shaDomain =\"$dom\" AND mxRec=1 AND (userId=$userId OR globalDomain=1)
								")->queryRow()) {
								$em=hash('sha512',base64_decode($email));
								if ($verifiedEmail = Yii::app()->db->createCommand(
									"SELECT addressHash,addr_type FROM addresses
								WHERE addressHash =\"$em\" AND userId=$userId
								")->queryRow()) {
									$addresses[]=array(
										'addressHash'=>$em,
										'mailKey'=>$pKey,
										'userId'=>$newUSerId,
										'addr_type'=>(int) $verifiedEmail['addr_type'],
										'vdId'=>(int) $verifiedDomains['id'],
										'v'=>2,
										'active'=>1
									);
								}

							}

						}

						if($userPlanSaved=Yii::app()->mongo->insert('addresses',$addresses))
						{
							$result['response']='success';

							$pr[':id']=$userId;
							$pr[':newId']=$newUSerId;

							if($user=Yii::app()->db->createCommand("UPDATE user SET version=2 WHERE id=:id AND modKey=:modKey")->execute($param))
							{
								Yii::app()->db->createCommand("UPDATE virtual_domains SET userId=:newId WHERE userId=:id")->execute($pr);

								//delete addresses from old table
								Yii::app()->db->createCommand("DELETE FROM addresses WHERE userId=:id")->execute(array(':id'=>$userId));


							}
						}

				}
			}
		}
		echo json_encode($result);
	}


}