<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class paymentApiV2 extends CFormModel
{
	public $userId, $userToken,$modKey;
	public $boxSize,$cDomain,$aliases,$dispEmails,$pgpStrength,$attSize,$importPGP,$contacts,$delaySend,$sendLimits,$recipPerMail,$folderExpiration,$secLog,$filtEmail;
    public $planSelector;

	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),

			array('planSelector', 'numerical', 'integerOnly'=>true,'allowEmpty' => false,'on'=>'bitcoinCreateOrder'),

			array('userId', 'match', 'pattern' => "/^[a-z0-9\d]{24}$/i", 'allowEmpty' => false,'message'=>'fld2upd','on'=>'bitcoinCreateOrder'),

			array('planSelector', 'numerical', 'integerOnly'=>true,'allowEmpty' => false,'on'=>'calculatePrice'),

			array('userId,modKey', 'safe', 'on'=>'calculatePrice'),


		);
	}

	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}
    public function calculatePriceOld($ret=null,$type='prorated')
    {

        $currentCost=0;
        $userId=Yii::app()->user->getId();
        $objects = Yii::app()->mongo->findById('user', $userId, array('cycleEnd' => 1,'planData'=>1,'monthlyCharge'=>1));
        $cycle=	$objects['cycleEnd']->sec;
        $now = time();

        $cycleDay=date('d',$cycle);


        $cycleYear=date('Y',$cycle);

        $monthNow=date('m',$now);
        $dayNow=date('d',$now);

        if($dayNow>=$cycleDay){
            $nxtMonth=date('Y-m-d',strtotime("$cycleYear-$monthNow-$cycleDay + 1 month"));
        }else{
            $nxtMonth=date('Y-m-d',strtotime("$cycleYear-$monthNow-$cycleDay"));
        }

        $your_date=strtotime($nxtMonth);

        $datediff = $your_date-$now;

        $prorateDays=floor($datediff/(60*60*24));

        if($prorateDays>365) $prorateDays=$prorateDays-365;

                if($prices=Yii::app()->db->createCommand("SELECT * FROM featurePrice")->queryAssoc('name')){
                    $result['response']="success";

                    if($this->boxSize>$prices['bSize']['minimumStartCharge']){

                        $block=($this->boxSize-$prices['bSize']['minimumStartCharge'])/$prices['bSize']['blockPerPriceMin'];
                        $price=$block*$prices['bSize']['price'];

                        if($block>5){
                            $currentCost+=$price-($prices['bSize']['discountMult'])*$price/100;
                        }else{
                            $currentCost+=$price;
                        }
                    }

                    if($this->cDomain>$prices['cDomain']['minimumStartCharge']){

                        $block=($this->cDomain-$prices['cDomain']['minimumStartCharge'])/$prices['cDomain']['blockPerPriceMin'];
                        $price=$block*$prices['cDomain']['price'];

                        if($block>0 && $block<=2){
                            $currentCost+=$price;
                        }else if($block>2)
                        {
                            $currentCost+=$price-($prices['cDomain']['discountMult'])*$price/100;
                        }
                    }

                    if($this->aliases>$prices['alias']['minimumStartCharge']){
                        $block=($this->aliases-$prices['alias']['minimumStartCharge'])/$prices['alias']['blockPerPriceMin'];
                        $price=$block*$prices['alias']['price'];

                        if($block>5){
                            $currentCost+=($price-($prices['alias']['discountMult'])*$price/100);
                        }else{
                            $currentCost+=$price;
                        }

                    }



                    if($this->pgpStrength>$prices['pgpStr']['minimumStartCharge']){
                        $block=($this->pgpStrength-$prices['pgpStr']['minimumStartCharge'])/$prices['pgpStr']['blockPerPriceMin'];
                        $price=$block*$prices['pgpStr']['price'];

                        if($block>5){
                            $currentCost+=($price-($prices['pgpStr']['discountMult'])*$price/100);
                        }else{
                            $currentCost+=$price;
                        }

                    }

                    if($this->attSize>$prices['attSize']['minimumStartCharge']){
                        $block=($this->attSize-$prices['attSize']['minimumStartCharge'])/$prices['attSize']['blockPerPriceMin'];
                        $price=$block*$prices['attSize']['price'];

                        if($block>1){
                            $currentCost+=($price-($prices['attSize']['discountMult'])*$price/100);
                        }else{
                            $currentCost+=$price;
                        }

                    }

                    if($this->dispEmails>$prices['dispos']['minimumStartCharge']){
                        $block=($this->dispEmails-$prices['dispos']['minimumStartCharge'])/$prices['dispos']['blockPerPriceMin'];
                        $price=$block*$prices['dispos']['price'];

                        if($block>3){
                            $currentCost+=($price-($prices['dispos']['discountMult'])*$price/100);
                        }else{
                            $currentCost+=$price;
                        }

                    }

                    if($this->importPGP>$prices['pgpImport']['minimumStartCharge']){
                        $block=($this->importPGP-$prices['pgpImport']['minimumStartCharge'])/$prices['pgpImport']['blockPerPriceMin'];
                        $price=$block*$prices['pgpImport']['price'];

                        if($block>3){
                            $currentCost+=($price-($prices['pgpImport']['discountMult'])*$price/100);
                        }else{
                            $currentCost+=$price;
                        }

                    }

                    if($this->contacts>$prices['contactList']['minimumStartCharge']){
                        $block=($this->contacts-$prices['contactList']['minimumStartCharge'])/$prices['contactList']['blockPerPriceMin'];
                        $price=$block*$prices['contactList']['price'];

                        if($block>3){
                            $currentCost+=($price-($prices['contactList']['discountMult'])*$price/100);
                        }else{
                            $currentCost+=$price;
                        }

                    }

                    if($this->delaySend>$prices['delaySend']['minimumStartCharge']){
                        $block=($this->delaySend-$prices['delaySend']['minimumStartCharge'])/$prices['delaySend']['blockPerPriceMin'];
                        $price=$block*$prices['delaySend']['price'];

                        if($block>3){
                            $currentCost+=($price-($prices['delaySend']['discountMult'])*$price/100);
                        }else{
                            $currentCost+=$price;
                        }

                    }



                    if($this->recipPerMail>$prices['recipPerMail']['minimumStartCharge']){
                        $block=($this->recipPerMail-$prices['recipPerMail']['minimumStartCharge'])/$prices['recipPerMail']['blockPerPriceMin'];
                        $price=$block*$prices['recipPerMail']['price'];

                        if($block>3){
                            $currentCost+=($price-($prices['recipPerMail']['discountMult'])*$price/100);
                        }else{
                            $currentCost+=$price;
                        }

                    }

                    if($this->sendLimits>$prices['sendLimits']['minimumStartCharge']){
                        $block=($this->sendLimits-$prices['sendLimits']['minimumStartCharge'])/$prices['sendLimits']['blockPerPriceMin'];
                        $price=$block*$prices['sendLimits']['price'];

                        if($block>3){
                            $currentCost+=($price-($prices['sendLimits']['discountMult'])*$price/100);
                        }else{
                            $currentCost+=$price;
                        }

                    }


                    if($this->folderExpiration>$prices['folderExpire']['minimumStartCharge']){
                        $block=($this->folderExpiration-$prices['folderExpire']['minimumStartCharge'])/$prices['folderExpire']['blockPerPriceMin'];
                        $price=$block*$prices['folderExpire']['price'];

                        if($block>3){
                            $currentCost+=($price-($prices['folderExpire']['discountMult'])*$price/100);
                        }else{
                            $currentCost+=$price;
                        }

                    }

                    if($this->secLog>$prices['secLog']['minimumStartCharge']){
                        $block=($this->secLog-$prices['secLog']['minimumStartCharge'])/$prices['secLog']['blockPerPriceMin'];
                        $price=$block*$prices['secLog']['price'];

                        if($block>3){
                            $currentCost+=($price-($prices['secLog']['discountMult'])*$price/100);
                        }else{
                            $currentCost+=$price;
                        }

                    }


                    if($this->filtEmail>$prices['filter']['minimumStartCharge']){
                        $block=($this->filtEmail-$prices['filter']['minimumStartCharge'])/$prices['filter']['blockPerPriceMin'];
                        $price=$block*$prices['filter']['price'];

                        if($block>3){
                            $currentCost+=($price-($prices['filter']['discountMult'])*$price/100);
                        }else{
                            $currentCost+=$price;
                        }

                    }


                }else{
                    $result['response']="fail";
                }


        $days = date("t");
        //print_r($days);
        $proratedCost=round($currentCost/$days*$prorateDays, 2);

        $result['data']['currentCost']=$proratedCost;
        $result['data']['monthlyCharge']=$currentCost;

        if($ret=='withPlan'){
            $result['data']['plan']=json_decode($objects['planData']);
            $result['data']['monthlyCharge']=round($objects['monthlyCharge']/100, 2);
            $result['data']['monthlyCharge']= round($objects['monthlyCharge']/100, 2);
        }
        if($ret=="return"){
            if($type=='prorated'){
                return $proratedCost;
            }else if($type=='full'){
                return $currentCost;
            }


        }else{
            echo json_encode($result);
        }

    }


	public function calculatePrice($ret=null,$type='prorated')
	{

		$currentCost=0;
		$userId=Yii::app()->user->getId();
		$objects = Yii::app()->mongo->findById('user', $userId, array('cycleEnd' => 1));

        if((int) $this->planSelector===77){
            $this->calculatePriceOld('withPlan');
            Yii::app()->end();
        }
       // Yii::app()->end();
		$cycle=	$objects['cycleEnd']->sec;
		$now = time();

		$cycleDay=date('d',$cycle);


		$cycleYear=date('Y',$cycle);

		$monthNow=date('m',$now);
		$dayNow=date('d',$now);

		if($dayNow>=$cycleDay){
			$nxtMonth=date('Y-m-d',strtotime("$cycleYear-$monthNow-$cycleDay + 1 month"));
		}else{
			$nxtMonth=date('Y-m-d',strtotime("$cycleYear-$monthNow-$cycleDay"));
		}

		$your_date=strtotime($nxtMonth);

		$datediff = $your_date-$now;

		$prorateDays=floor($datediff/(60*60*24));

		if($prorateDays>365) $prorateDays=$prorateDays-365;

        $prices=Yii::app()->params['params']['planData'][$this->planSelector]['price'];


		$days = date("t");
		//print_r($days);
		$proratedCost=round($prices/$days*($prorateDays+1), 2);

        $result['response']="success";
		$result['data']['currentCost']=$proratedCost/100;
		$result['data']['monthlyCharge']=$prices/100;
        $result['data']['plan']=Yii::app()->params['params']['planData'][$this->planSelector];
        $currentCost=Yii::app()->params['params']['planData'][$this->planSelector]['price'];

		if($ret=="return"){
			if($type=='prorated'){
				return $proratedCost;
			}else if($type=='full'){
				return $currentCost;
			}


		}else{
			echo json_encode($result);
		}

	}

	public function paypalCreateOrder()
	{

		//$data=file_get_contents('/work/scryptmail/callBackPaypConfirmed.txt');
		//$data=file_get_contents('/work/scryptmail/callBackPaypChargeBack.txt');
		$data=file_get_contents('/work/scryptmail/callBackPaypChargeBackRefunded.txt');
		parse_str($data, $exploded);
		print_r($exploded);


	}


	public function bitcoinCreateOrder()
	{
		$price=$this->calculatePrice('return','full');


		//todo recalculate price prorated
		//echo 'ssss';
		$result['response']="success";

		//woring POST
		$Key = Yii::app()->params['coinKey'];
		$Secret = Yii::app()->params['coinSecret'];

		$time = time();
		//echo $time;
		$urlapi = "https://api.coinbase.com/v2/checkouts";

		//$Key = "--------------";
		//$Secret = "------------";
		$fecha = new DateTime();
		$timestamp = $fecha->getTimestamp();
		$request="/v2/checkouts";
		$prebody=array(
			"amount"=> Yii::app()->params['params']['planData'][$this->planSelector]['price']/100,
			"currency"=> "USD",
			"name"=> "SCRYPTmail Refill",
			"customer_defined_amount"=>"true",
			"description"=> "Please enter amount",
			"metadata"=> array(
				"customer_id"=>$this->userId
			)
		);
		$body=json_encode($prebody);
		$method="POST";
		$Datas = $timestamp . $method . $request . $body;
		$hmacSig = hash_hmac('sha256',$Datas,$Secret);
		$curl = curl_init($urlapi);
		curl_setopt($curl,CURLOPT_HTTPHEADER,array
		(
			'Content-Type: application/json',
			'CB-ACCESS-KEY: '.$Key,
			'CB-VERSION: 2015-07-07',
			'CB-ACCESS-TIMESTAMP: '. $timestamp,
			'CB-ACCESS-SIGN: '.$hmacSig));
		curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl, CURLOPT_CAINFO, Yii::app()->basePath."/certs/ca-coinbase.crt");
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

		if($resp = curl_exec($curl)){
			//print_r($resp);
		}else{
			$result['response']="fail";
			//die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
		}

		curl_close($curl);



		try {
			$response=json_decode($resp,true);
			//print_r($response);
			$result['data']['embed_code']=$response['data']['embed_code'];

		} catch (Exception $e) {
			$result['response']="fail";
		}
		echo json_encode($result);
		//print_r($result);

	}

}