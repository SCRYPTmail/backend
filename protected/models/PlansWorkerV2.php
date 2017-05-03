<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class PlansWorkerV2 extends CFormModel
{

	public $userId, $userToken,$modKey;
	public $boxSize,$recipPerMail,$cDomain,$aliases,$dispEmails,$pgpStrength,$attSize,$importPGP,$contacts,$delaySend,$sendLimits,$folderExpiration,$secLog,$filtEmail;

	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),

			array(',aliases,boxSize,cDomain,dispEmails,pgpStrength,attSize,importPGP,contacts,delaySend,sendLimits,recipPerMail,folderExpiration,secLog,filtEmail', 'numerical', 'integerOnly'=>true,'allowEmpty' => false,'on'=>'savePlan'),

			array('userId', 'match', 'pattern' => "/^[a-z0-9\d]{24}$/i", 'allowEmpty' => false,'message'=>'fld2upd','on'=>'savePlan'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false,'message'=>'fld2upd','on'=>'savePlan,claimFree'),
		);
	}

	public function chkToken(){
		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}


	public function claimFree($userId)
	{
		//print_r($userId);

		$result['response']="fail";

		if ($plan = Yii::app()->mongo->findById('user', $userId, array('created' => 1,'creditUsed'=>1))) {

			$goodOld=strtotime('2015/11/19');
			$amount=200;

			if($goodOld>$plan['created']->sec){
				$amount=500;

			}

			$newPlan = array(
				'balance' => $amount,
				'creditUsed' => true
			);
			$criteria = array("_id" => new MongoId($userId), 'modKey' => hash('sha512', $this->modKey),'creditUsed'=>false);

			if ($user = Yii::app()->mongo->update('user', $newPlan, $criteria)) {
				$result['response']="success";
			} else {
				$result['data'] = "alrdUsed";
			}

		}

		echo json_encode($result);

	}

	public function retrievePrice()
	{
		//$param[':userId']=$userId;

		if($prices=Yii::app()->db->createCommand("SELECT * FROM featurePrice")->queryAll()){

			$result['response']="success";
			$result['data']=$prices;

		}else{
			$result['response']="fail";
		}



		echo json_encode($result);
	}

	public function retrieveUserPlan($userId)
	{
		//$param[':userId']=$userId;

			if ($planObjects = Yii::app()->mongo->findById('user', $userId, array('planData' => 1,'pastDue'=>1,'cycleStart'=>1,'cycleEnd'=>1,'created'=>1,'creditUsed'=>1,'balance'=>1,'alrdPaid'=>1,'monthlyCharge'=>1))) {

				$plan['planData']=json_decode($planObjects['planData'],true);

				$result['response']="success";
				$result['data']=$plan;
				$result['data']['pastDue']=$planObjects['pastDue'];
				$result['data']['cycleStart']=$planObjects['cycleStart']->sec;
				$result['data']['cycleEnd']=$planObjects['cycleEnd']->sec;
				$result['data']['created']=$planObjects['created']->sec;
				$result['data']['creditUsed']=$planObjects['creditUsed'];
				$result['data']['balance']=$planObjects['balance']/100;
				$result['data']['alrdPaid']=$planObjects['alrdPaid']/100;
				$result['data']['monthlyCharge']=$planObjects['monthlyCharge']/100;


				$paymentApiV2 =new paymentApiV2();

				$result['data']['currentCost']=$paymentApiV2->calculatePrice('return','prorated');
				$currentversion=Yii::app()->db->createCommand("SELECT systemVersion FROM versions")->queryRow();

				$result['data']['currentVersion']=(int) $currentversion['systemVersion'];


			}else{
				$result['response']="fail";
			}


		echo json_encode($result);
	}

	public function savePlan($userId)
	{
		//todo add history plan option(add money, add options, time)
	//check how much already paid this cycle calculate plan price if> get difference and if available balance enough to cover if yes, substract it and save new plan if not give error to fill balance

		$result['response']="success";

		if ($plan = Yii::app()->mongo->findById('user', $userId, array('planData' => 1,'alrdPaid'=>1,'balance'=>1,''))) {

			$paymentApiV2 =new paymentApiV2('calculatePrice');
			$paymentApiV2->attributes=$this->attributes;

			$price = $paymentApiV2->calculatePrice('return')*100;

			if ($price>0 && $price - $plan['alrdPaid'] > $plan['balance']) {
				$result['response'] = "fail";
				$result['data'] = "insBal";
				$result['need'] = ($price - $plan['alrdPaid']-$plan['balance'])/100;

			} else if ($price - $plan['alrdPaid'] <= $plan['balance'] || $price===0.00) {

				$monthCharge=round($paymentApiV2->calculatePrice('return', 'full')*100);
				$newPlan = array(
					'bSize' => $this->boxSize,
					'cDomain' => $this->cDomain,
					'alias' => $this->aliases,
					'pgpStr' => $this->pgpStrength,
					'attSize' => $this->attSize,
					'dispos' => $this->dispEmails,
					'pgpImport' => $this->importPGP,
					'contactList' => $this->contacts,
					'delaySend' => $this->delaySend,
					'sendLimits' => $this->sendLimits,
					'recipPerMail'=>$this->recipPerMail,
					'folderExpire' => $monthCharge>0?$this->folderExpiration:0,
					'secLog' => $this->secLog,
					'filter' => $this->filtEmail,
				);
				$difer = $price - $plan['alrdPaid'];


				$userObj = array(
					"balance" => round($plan['alrdPaid'] < $price ? $plan['balance'] - ($difer) : $plan['balance']),
					"alrdPaid" => round($plan['alrdPaid'] < $price ? $price : $plan['alrdPaid']),
					"monthlyCharge" => $monthCharge,
					"planData" => json_encode($newPlan),
					"planUpdatedAt" => new MongoDate(strtotime('now'))
				);

				$criteria = array("_id" => new MongoId($userId), 'modKey' => hash('sha512', $this->modKey));

				if ($user = Yii::app()->mongo->update('user', $userObj, $criteria)) {
					//$result['data']="insBal";
				} else {
					$result['response'] = "fail";
					$result['data'] = "failToSave";
				}

				$data['type'] = 2;
				$data['description'] = "planChange";
				$data['amount'] = $difer;
				$data['author'] = 2;
				$data['orderId'] = "";
				$data['callbackData'] = json_encode($newPlan);

				$this->savePlanHistory($userId, $data);
			}
		}else{
			$result['response'] = "fail";
			$result['data'] = "failToSave";
		}

		echo json_encode($result);

	}

	public function savePlanHistory($userId,$data)
	{
		$param[':userId']=$userId;
		$param[':transType']=$data['type'];
		$param[':description']=$data['description'];
		$param[':amount']=$data['amount'];
		$param[':author']=$data['author'];
		$param[':orderId']=$data['orderId'];
		$param[':callbackData']=$data['callbackData'];


		Yii::app()->db->createCommand("INSERT INTO userPaymentHistory (userId,transType,description,amount,author,orderId,callbackData) VALUE(:userId,:transType,:description,:amount,:author,:orderId,:callbackData)")->execute($param);

	}

}