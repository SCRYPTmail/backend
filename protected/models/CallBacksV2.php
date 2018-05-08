<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class CallBacksV2 extends CFormModel
{

	public $email;

	public function rules()
	{
		return array(
			//array('userToken', 'chkToken'),
		);
	}


	public function paypal()
	{
		/* test callback
		$jEncodedData=file_get_contents('php://input');
		$myfile = fopen("test.txt", "w") or die("Unable to open file!");
		fwrite($myfile,json_encode($jEncodedData));
		fclose($myfile);
				echo 'ok';
		Yii::app()->end();
		*/



		//todo input for live
		if (Yii::app()->params['production']) {
			$jEncodedData=file_get_contents('php://input');
		}else{
			//$jEncodedData=file_get_contents('callBackPaypConfirmed.txt');
			$jEncodedData=file_get_contents('callBackPaypChargeBackRefunded.txt');
		}



		$ch = curl_init('https://www.paypal.com/cgi-bin/webscr');
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "cmd=_notify-validate&".$jEncodedData);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

		$verify = curl_exec($ch);
		curl_close($ch);

		parse_str($jEncodedData, $jDecodedData);
		//print_r($jDecodedData);
		if(strtolower($verify)==="verified"){
			//print_r($jDecodedData);

			$status=$jDecodedData['payment_status'];

            if(isset($jDecodedData['item_number']))
            {
                $type=$jDecodedData['item_number'];
            }

			//print_r($status);

			$data['amountCents']=$jDecodedData['mc_gross']*100;
			$data['amountCurrency']=$jDecodedData['mc_currency'];

			$data['userId']=$jDecodedData['custom'];
			$data['status']=$jDecodedData['payment_status'];

			if($status=="Completed"){
				$data['orderId']=$jDecodedData['txn_id'];
			}else if($status=="Refunded"){
				$data['orderId']=$jDecodedData['parent_txn_id'];
			}else if($status=="Failed"){
				$data['orderId']=$jDecodedData['txn_id'];
			}else if($status=="Reversed") {
                $data['orderId'] = $jDecodedData['parent_txn_id'];
            }else if($status=="Denied") {
                $data['orderId'] = $jDecodedData['txn_id'];
            }



			if($status=="Completed" && $data['amountCurrency']=="USD" && strlen($data['userId'])==24){
				$param[':userId']=$data['userId'];
				$param[':balance']=$data['amountCents'];

				$params[':orderId']=$data['orderId'];

				if(!Yii::app()->db->createCommand("SELECT orderId FROM userPaymentHistory WHERE orderId=:orderId")->queryRow(true, $params)){

					$currentData=Yii::app()->mongo->findById('user',$data['userId'],array('balance'=>1,'pastDue'=>1,'alrdPaid'=>1));

					$alrdPaid=$currentData['alrdPaid'];

					if($currentData['balance']<0 && $data['amountCents']+$currentData['balance']>0){
						$alrdPaid=$currentData['alrdPaid']-$currentData['balance'];
					}else if($currentData['balance']<0 && $data['amountCents']+$currentData['balance']<0){
						$alrdPaid=$currentData['alrdPaid']+$data['amountCents'];
					}

					if($currentData['balance']>=0 || $data['amountCents']+$currentData['balance']>=0){
						$pastDue=0;
					}else{
						$pastDue=1;
					}


					$userObj=array(
						"planUpdatedAt"=>new MongoDate(strtotime('now')),
						"pastDue"=>$pastDue,
						"alrdPaid"=>$alrdPaid
					);
					$incremental=array(
						"balance"=>$data['amountCents']
					);

					$criteria=array("_id" => new MongoId($data['userId']));

					Yii::app()->mongo->update('user', $userObj, $criteria,$incremental);

					$histData['type']="1";
					$histData['description']='Load Funds';
					$histData['amount']=$data['amountCents'];
					$histData['author']=3;
					$histData['orderId']=$data['orderId'];
					$histData['callbackData']=json_encode($jDecodedData);

					$stats=new StatsV2;
					$stats->counter('paymentPayPalRcvd',$data['amountCents']);

					PlansWorkerV2::savePlanHistory($data['userId'],$histData);

				}else{

					$histData['type']="3";
					$histData['description']="Order Exist in System";
					$histData['amount']=$data['amountCents'];
					$histData['author']=3;
					$histData['orderId']=$data['orderId'];
					$histData['callbackData']=json_encode($jDecodedData);;

					PlansWorkerV2::savePlanHistory($data['userId'],$histData);
				}

			}else if($status=="Refunded" && $data['amountCurrency']=="USD" && strlen($data['userId'])==24){

				$param[':userId']=$data['userId'];
				$param[':balance']=$data['amountCents'];

				$params[':orderId']=$data['orderId'];

				if(!Yii::app()->db->createCommand("SELECT orderId FROM userPaymentHistory WHERE orderId=:orderId AND description='Refund Order'")->queryRow(true, $params)){

					$currentData=Yii::app()->mongo->findById('user',$data['userId'],array('balance'=>1,'pastDue'=>1,'alrdPaid'=>1));

					$left=$currentData['balance']+$data['amountCents'];

					if($left<0){
						$pastDue=1;
						$alrdPaid=$currentData['alrdPaid']+$left;
					}else{
						$pastDue=0;
						$alrdPaid=$currentData['alrdPaid'];
					}

					$userObj=array(
						"planUpdatedAt"=>new MongoDate(strtotime('now')),
						"pastDue"=>$pastDue,
						"alrdPaid"=>$alrdPaid
					);
					$incremental=array(
						"balance"=>$data['amountCents']
					);

					$criteria=array("_id" => new MongoId($data['userId']));


					Yii::app()->mongo->update('user', $userObj, $criteria,$incremental);
					//Yii::app()->end();

					//Yii::app()->db->createCommand("UPDATE userFeatures SET balance=balance+:balance,updated=NOW() WHERE userId=:userId")->execute($param);

					$histData['type']="5";
					$histData['description']='Refund Order';
					$histData['amount']=$data['amountCents'];
					$histData['author']=3;
					$histData['orderId']=$data['orderId'];
					$histData['callbackData']=json_encode($jDecodedData);

					PlansWorkerV2::savePlanHistory($data['userId'],$histData);

					$stats=new StatsV2;
					$stats->counter('paymentPayPalRefunded',$data['amountCents']);

				}else{

					$histData['type']="3";
					$histData['description']="Refund already processed";
					$histData['amount']=$data['amountCents'];
					$histData['author']=3;
					$histData['orderId']=$data['orderId'];
					$histData['callbackData']=json_encode($jDecodedData);;

					PlansWorkerV2::savePlanHistory($data['userId'],$histData);
				}

			}else if($status!="Canceled_Reversal" && strtolower($type)!=="donation" && $type!="Please consider donating at least $0.3 to cover PayPal processing fees"){

				$histData['type']="4";
				$histData['description']="Order mispaid,expired or wrong currency";
				$histData['amount']=$data['amountCents'];
				$histData['author']=3;
				$histData['orderId']=$data['orderId'];
				$histData['callbackData']=json_encode($jDecodedData);;

				PlansWorkerV2::savePlanHistory($data['userId'],$histData);
			}

		}

	}
	public function bitcoin($userId)
	{
/*
		$jEncodedData=file_get_contents('php://input');

		$current=$data;

		$myfile = fopen("callback.txt", "w") or die("Unable to open file!");

		fwrite($myfile,json_encode($current));

//fwrite($myfile, $txt);
		fclose($myfile);

		echo 'ok';
		*/
		if (Yii::app()->params['production']) {
			$jEncodedData=file_get_contents('php://input');
		}else{
			$jEncodedData=file_get_contents('callb');
		}

		//$jEncodedData=file_get_contents('/work/scryptmail/callbackMiss.txt');
		//$jEncodedData=file_get_contents('/work/scryptmail/callBackExp.txt');
		//


		$jDecodedData=json_decode($jEncodedData,true);

		$status=$jDecodedData['event']['data']['payments'][0]['status'];

		//mispaid
		//completed
		//expired


/*		if($status=="mispaid" || $status=="expired"){

			$data['amountCents']=$jDecodedData['order']['mispaid_native']['cents'];
			$data['amountCurrency']=$jDecodedData['order']['mispaid_native']['currency_iso'];


		}else*/

        if($status=="CONFIRMED" ){

			$data['amountCents']=$jDecodedData['event']['data']['payments'][0]['value']['local']['amount']*100;
			$data['amountCurrency']=$jDecodedData['event']['data']['payments'][0]['value']['local']['currency'];
		}
		$data['userId']=$jDecodedData['event']['data']['metadata']['customer_id'];
		$data['status']=$status;
		$data['orderId']=$jDecodedData['event']['data']['payments'][0]['transaction_id'];

		if($status=="CONFIRMED"  && $data['amountCurrency']=="USD"){
			$param[':userId']=$data['userId'];
			$param[':balance']=$data['amountCents'];

			$params[':orderId']=$data['orderId'];

			if(!Yii::app()->db->createCommand("SELECT orderId FROM userPaymentHistory WHERE orderId=:orderId")->queryRow(true, $params)){


				$currentData=Yii::app()->mongo->findById('user',$data['userId'],array('balance'=>1,'pastDue'=>1,'alrdPaid'=>1));

				$alrdPaid=$currentData['alrdPaid'];

				if($currentData['balance']<0 && $data['amountCents']+$currentData['balance']>0){
					$alrdPaid=$currentData['alrdPaid']-$currentData['balance'];

				}else if($currentData['balance']<0 && $data['amountCents']+$currentData['balance']<0){
					$alrdPaid=$currentData['alrdPaid']+$data['amountCents'];
				}

				if($currentData['balance']>=0 || $data['amountCents']+$currentData['balance']>=0){
					$pastDue=0;
				}else{
					$pastDue=1;
				}

				$userObj=array(
					"planUpdatedAt"=>new MongoDate(strtotime('now')),
					"pastDue"=>$pastDue,
					"alrdPaid"=>$alrdPaid
				);
				$incremental=array(
					"balance"=>$data['amountCents']
				);

				$criteria=array("_id" => new MongoId($data['userId']));


				Yii::app()->mongo->update('user', $userObj, $criteria,$incremental);


				$histData['type']="1";
				$histData['description']='Load Funds';
				$histData['amount']=$data['amountCents'];
				$histData['author']=3;
				$histData['orderId']=$data['orderId'];
				$histData['callbackData']=$jEncodedData;


				PlansWorkerV2::savePlanHistory($data['userId'],$histData);

				$stats=new StatsV2;
				$stats->counter('paymentBitcoinRcvd',$data['amountCents']);

			}else{


				$histData['type']="3";
				$histData['description']="Order Exist in System";
				$histData['amount']=$data['amountCents'];
				$histData['author']=3;
				$histData['orderId']=$data['orderId'];
				$histData['callbackData']=$jEncodedData;

				PlansWorkerV2::savePlanHistory($data['userId'],$histData);
			}

		}else if(isset($data['amountCents'])){

			$histData['type']="4";
			$histData['description']="Order mispaid,expired or wrong currency";
			$histData['amount']=$data['amountCents'];
			$histData['author']=3;
			$histData['orderId']=$data['orderId'];
			$histData['callbackData']=$jEncodedData;

			PlansWorkerV2::savePlanHistory($data['userId'],$histData);
		}
		//print_r($jDecodedData);
		//print_r($data);
	}




}
