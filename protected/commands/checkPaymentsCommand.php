<?php

class CheckPaymentsCommand extends CFormModel
{
	public $mails;


	public function rules()
	{
	}


	public function run()
	{
		/**
		 * Process accounts that is subjected to new month charge
		 *
		 * 1) date is less than today
		 * 2) update balance, reset charge
		 */

		$today=new MongoDate(strtotime('now'));

		$mngDataAgregate=array(
			'cycleEnd'=>array(
				'$lt'=>$today
			),
			//'monthlyCharge'=>0
		);

		if($ref=Yii::app()->mongo->findAll('user',$mngDataAgregate,array('_id'=>1,'monthlyCharge'=>1,'balance'=>1),200)){

			//print_r($ref);

			//Yii::app()->end();

			//$ref=Yii::app()->mongo->findAll('user',$mngDataAgregate,array('_id'=>1,'balance'=>1,'monthlyCharge'=>1),10)
			foreach($ref as $userId=>$data){

				if($userData=Yii::app()->mongo->findById('user',$userId,array('_id'=>1,'balance'=>1,'monthlyCharge'=>1)))
				{

					$newBalance=0;
					if($userData['monthlyCharge']<0){
						$newBalance=$userData['balance']+$userData['monthlyCharge'];
					}else{
						$newBalance=$userData['balance']-$userData['monthlyCharge'];
					}
					if($newBalance>=0){
						$alrdPaid=$userData['monthlyCharge'];
					}else if($userData['balance']>=0){
						$alrdPaid=$userData['balance'];
					}else{
						$alrdPaid=0;
					}


					if($newBalance<0){
						$pastDue=1;
					}else{
						$pastDue=0;
					}


					$userObj=array(
						"cycleStart"=>$today,
						"cycleEnd"=>new MongoDate(strtotime('now' ."+1 month")),
						"balance"=>$newBalance,
						"alrdPaid"=>$alrdPaid,
						"pastDue"=>$pastDue
					);

					$criteria=array("_id" => new MongoId($userId));

					Yii::app()->mongo->update('user', $userObj, $criteria);

				}


			}
		}

	}




}

