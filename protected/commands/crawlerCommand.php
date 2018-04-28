<?php

class CrawlerCommand extends CFormModel
{
	public $mails;


	public function rules()
	{
	}


	public function run()
	{
		$this->postOffice(); //send mail

	}


	/**
	 * @param $emailData {array} table row with email
	 * @return bool
	 */

	public function postOffice()
	{
		//todo add emailSize field with attachment

		if (!Yii::app()->db->createCommand("SELECT * FROM crawler WHERE action='takingMail'")->queryRow()) {
			Yii::app()->db->createCommand("INSERT INTO crawler (action,active) VALUES('takingMail',1)")->execute();
			if ($email = Yii::app()->db->createCommand("SELECT * FROM mail2sent WHERE failed IS NULL LIMIT 500")->queryAll()) {
				if (count($email) > 0) {
					foreach ($email as $index => $emailData) {

						$message2remove = $emailData['id'];
						switch ($emailData['destination']) {
							case "1":
								$result = $this->fetchEmailClearText($emailData);

								if ($result) {
									Yii::app()->db->createCommand("DELETE FROM mail2sent WHERE id=:id")->execute(array(":id" => $message2remove));
								} else {
									Yii::app()->db->createCommand("UPDATE mail2sent SET failed=1 WHERE id=:id")->execute(array(":id" => $message2remove));
								}

								break;

							case "2":
								$result = $this->fetchEmailPin($emailData);

								if ($result) {
									Yii::app()->db->createCommand("DELETE FROM mail2sent WHERE id=:id")->execute(array(":id" => $message2remove));
								} else {
									Yii::app()->db->createCommand("UPDATE mail2sent SET failed=1 WHERE id=:id")->execute(array(":id" => $message2remove));
								}
								break;

							case "3":
								$result = $this->fetchEmailPGP($emailData);

								if ($result) {
									Yii::app()->db->createCommand("DELETE FROM mail2sent WHERE id=:id")->execute(array(":id"=>$message2remove));
								} else {
									Yii::app()->db->createCommand("UPDATE mail2sent SET failed=1 WHERE id=:id")->execute(array(":id"=>$message2remove));
								}
								break;

							case "4":
								$result = $this->fetchEmailInternal($emailData);

								if ($result) {
									Yii::app()->db->createCommand("DELETE FROM mail2sent WHERE id=:id")->execute(array(":id"=>$message2remove));
								} else {
									Yii::app()->db->createCommand("UPDATE mail2sent SET failed=1 WHERE id=:id")->execute(array(":id"=>$message2remove));
								}
								break;

						}
					}

				}

			}
			Yii::app()->db->createCommand("DELETE FROM crawler")->execute();

		}
	}
	public function fetchEmailInternal($emailData)
	{
		//print_r($emailData);

		if ($emailObj = json_decode($emailData['email'], true)) {


			$toCC=true;
			$toBCC1=true;


			if (isset($emailObj['toCCrcpt']['recipients']) && count($emailObj['toCCrcpt']['recipients']) > 0) {

				foreach($emailObj['toCCrcpt']['recipients'] as $index=>$email64){

					$SavingUserDataV2 = new SavingUserDataV2();
                    $ema=$SavingUserDataV2->extract_email_address(base64_decode($email64));
                    if(isset($ema[0])){

                        $email= $ema[0];

                        $emailSHA512=hash('sha512',$email);
                        $person[] = array(
                            "meta" => $emailObj['toCCrcpt']['meta'],
                            "body" => $emailObj['toCCrcpt']['email'],
                            "modKey" => $emailObj['modKey'],
                            "rcpnt"=> substr($emailSHA512,0,10),
                            'file'=>json_encode($emailObj['attachments']),
                            'emailSize'=>strlen($emailObj['toCCrcpt']['email'])+$emailObj['aSize'],
                            "expireAfter" => new MongoDate(strtotime('now' . '+ 1 year')),
                            "timeSent"=>time()
                        );

                    }else{
                    }

				}

				if ($mId = Yii::app()->mongo->insert('mailQv2', $person)) {
					$toCC=true;
				}else{
					$toCC=false;
				}

			}

			if (isset($emailObj['bccRcptV1']) && count($emailObj['bccRcptV1']) > 0) {
				//print_r($emailObj['bccRcptV1']);
				$toBCC1=$this->sendIntV1($emailObj['bccRcptV1'], $emailObj['sender'],$emailObj['attachments'],$emailObj['aSize']);


			}
			if (isset($emailObj['toCCrcptV1']) && count($emailObj['toCCrcptV1']) > 0) {
				//print_r($emailObj['toCCrcptV1']);
				$toBCC1=$this->sendIntV1($emailObj['toCCrcptV1'], $emailObj['sender'],$emailObj['attachments'],$emailObj['aSize']);


			}

			if($toCC && $toBCC1){
				return true;
			}else{
				return false;
			}
			//print_r($emailObj);

		}

		return true;
	}


	public function fetchEmailPin($emailData)
	{

		if ($emailObj = json_decode($emailData['email'], true)) {
			//print_r($emailObj);
			$sendTo = false;
			if (count($emailObj['toCCrcpt']['recipients']) > 0) {
				$sendTo = $this->sendEmailPin(
					'cc',
					$emailObj['toCCrcpt']['recipients'],
					$emailObj['sender'],
					$emailObj['subject'],
					$emailObj['toCCrcpt']['email'],
					$emailObj['modKey'],
					$emailObj['pKeyHash']
				);

			} else {
				$sendTo = true;
			}

			$sendBCC = false;

			if (count($emailObj['bccRcpt']) > 0) {
				$sendBCC = $this->sendEmailPin(
					'bcc',
					$emailObj['bccRcpt'],
					$emailObj['sender'],
					$emailObj['subject'],
					'emailEmpty',
					$emailObj['modKey'],
					$emailObj['pKeyHash']
				);

			} else {
				$sendBCC = true;
			}

			if ($sendTo && $sendBCC) {
				//return if send or failed
				return true;
			} else {
				return false;
			}

		} else {
			//return if can not decode
			return false;
		}

		//print_r($emailObj);
	}

	public function sendEmailPin($type, $to, $from, $subject, $emailText, $modKey, $pinHash)
	{

		if ($type === "cc") {
			//$toArr=explode(',',$to);
			$ccBool = true;
			foreach ($to as $recipient => $email) {
				//print_r(SavingUserDataV2::extract_email_address(base64_decode($email))[0]);
				$SavingUserDataV2 = new SavingUserDataV2();

				$resEm=$SavingUserDataV2->extract_email_address(base64_decode($email));
				if(isset($resEm[0])){
					$person[] = array(
						"body" => $emailText,
						"modKey" => $modKey,
						"pinHash" => $pinHash,
						"recipientHash"=>hash('sha256',$SavingUserDataV2->extract_email_address(base64_decode($email))[0]),
						"tryCounter" => 0,
						"expireAfter" => new MongoDate(strtotime('now' . '+ 1 year'))
					);

					if ($rowId = Yii::app()->mongo->insert('mailQv2', $person)) {
						$message['text'] = "";
						$message['html'] = file_get_contents(Yii::app()->basePath . '/views/templates/emailWithPin.php');
						$message['html'] = str_replace('*|SENDER|*', htmlentities(base64_decode($from)), $message['html']);
						$message['html'] = str_replace('*|LINK_TO_MESSAGE|*', 'https://'.Yii::app()->params['params']['registeredDomain'].'/#retrieveEmailV2/' . $rowId[0], $message['html']);
						$message['html'] = base64_encode($message['html']);

						if ($ccBool) {
							$ccBool = $this->sendEmail(array($email), array(), $from, $subject, $message);
						}

					}

				}

				unset($person);

			}
			return $ccBool;

		} else if ($type === "bcc") {

			$bccBool = true;
			foreach ($to as $recipient => $email) {
				//print_r(SavingUserDataV2::extract_email_address(base64_decode($recipient))[0]);
				$SavingUserDataV2 = new SavingUserDataV2();

				$resEm=$SavingUserDataV2->extract_email_address(base64_decode($recipient));
				if(isset($resEm[0])){
					$person[] = array(
						"body" => $email,
						"modKey" => $modKey,
						"pinHash" => $pinHash,
						"recipientHash"=>hash('sha256',$SavingUserDataV2->extract_email_address(base64_decode($recipient))[0]),
						"tryCounter" => 0,
						"expireAfter" => new MongoDate(strtotime('now' . '+ 1 year'))
					);

					if ($rowId = Yii::app()->mongo->insert('mailQv2', $person)) {
						$message['text'] = "";
						$message['html'] = file_get_contents(Yii::app()->basePath . '/views/templates/emailWithPin.php');
						$message['html'] = str_replace('*|SENDER|*', htmlentities(base64_decode($from)), $message['html']);
						$message['html'] = str_replace('*|LINK_TO_MESSAGE|*', 'https://'.Yii::app()->params['params']['registeredDomain'].'/#retrieveEmailV2/' . $rowId[0], $message['html']);
						$message['html'] = base64_encode($message['html']);
						if ($bccBool) {
							$bccBool = $this->sendEmail(array($recipient), array(), $from, $subject, $message);
						}


					}

				}




				unset($person);

			}
			return $bccBool;
			//print_r($email);

		}


	}


	public function fetchEmailPGP($emailData)
	{
		if ($emailObj = json_decode($emailData['email'], true)) {
			$toSend = true;
			//print_r($emailObj);

			if (isset($emailObj['toCCrcpt']['recipients']) && count($emailObj['toCCrcpt']['recipients']) > 0) {
				//print_r('toPGPv2');
				$body['text'] = $emailObj['toCCrcpt']['email'];
				//$body['html'] = base64_encode("<pre>". base64_decode($emailObj['toCCrcpt']['email']).'</pre>/');

				$toSend = $this->sendEmailPGP(
					$emailObj['toCCrcpt']['recipients'],
					array(),
					$emailObj['sender'],
					$emailObj['subject'],
					$body
				);

			}
			$toSendV1 = true;
			if (isset($emailObj['toCCrcptV1']) && count($emailObj['toCCrcptV1']) > 0) {
				$this->sendIntV1($emailObj['toCCrcptV1'], $emailObj['sender']);
			}


			$toBccv1 = true;
			if (isset($emailObj['bccRcptV1']) && count($emailObj['bccRcptV1']) > 0) {
				$this->sendIntV1($emailObj['bccRcptV1'], $emailObj['sender']);
			}


			if (isset($emailObj['bccRcpt']) && count($emailObj['bccRcpt']) > 0) {
				foreach ($emailObj['bccRcpt'] as $email64 => $emailPGP64) {
					$body['text'] = $emailPGP64;
					//$body['html'] = base64_encode("<pre>".base64_decode($emailPGP64).'</pre>/');

					$toSendV1 = $this->sendEmailPGP(
						array($email64),
						array(),
						$emailObj['sender'],
						$emailObj['subject'],
						$body
					);

				}
			}
			//todo validate sending
			return true;
			//print_r($emailObj);


		} else {
			return false;
		}

	}

	public function tryEncryptOld($publicKey, $data)
	{
		openssl_public_encrypt($data, $encrypted, base64_decode($publicKey), OPENSSL_PKCS1_OAEP_PADDING);
		//$data = bin2hex($encrypted);

		return $encrypted;
	}


	/**
	 * @param $emailData
	 * @return bool
	 */
	public function fetchEmailClearText($emailData)
	{

		if ($emailObj = json_decode($emailData['email'], true)) {
			//print_r($emailObj);
			$sendTo = false;
			if (count($emailObj['mailData']['meta']['to']) > 0 || count($emailObj['mailData']['meta']['cc']) > 0) {
				$sendTo = $this->sendEmail(
					$emailObj['mailData']['meta']['to'],
					$emailObj['mailData']['meta']['cc'],
					$emailObj['mailData']['meta']['from'],
					$emailObj['mailData']['meta']['subj'],
					$emailObj['mailData']['body']

				);

			} else {
				$sendTo = true;
			}

			$sendBCC = false;
			if (count($emailObj['mailData']['meta']['bcc']) > 0) {

				foreach($emailObj['mailData']['meta']['bcc'] as $email64){
					$sendBCC = $this->sendEmail(
						array($email64),
						array(),
						$emailObj['mailData']['meta']['from'],
						$emailObj['mailData']['meta']['subj'],
						$emailObj['mailData']['body']

					);
				}


			} else {
				$sendBCC = true;
			}

			if ($sendTo && $sendBCC) {
				return true;
			}

		} else {
			return false;
		}

	}


    public function sendEmailPGP($to, $cc, $from, $subject, $body)
    {

        foreach ($to as $index => $row) {
            $to[$index] = base64_decode($row);
        }
        foreach ($cc as $index => $row) {
            $cc[$index] = base64_decode($row);
        }
        $subject = base64_decode($subject);
        $from = base64_decode($from);
        $body['text'] = base64_decode($body['text']);


        //if (Yii::app()->params['production']) {


        $boundary = uniqid('np');

        $eol = "\r\n";

        $headers = "MIME-Version: 1.0" . $eol;
        $headers .= "From: " . $from . $eol . "Reply-To: " . $from . $eol;

        $headers .= "To: " . implode(", ", $to) . $eol;
        if (count($cc) > 0) {
            $headers .= "CC: " . implode(", ", $cc) . $eol;
        }



        $headers .= "Content-Type: multipart/encrypted; boundary=$boundary" . $eol.'protocol="application/pgp-encrypted"' . $eol. $eol;

        $message = 'This is an OpenPGP/MIME encrypted message (RFC 4880 and 3156)'. $eol;
        $message .= "--$boundary" . $eol;
        $message .= 'Content-Type: application/pgp-encrypted; charset="UTF-8"'. $eol;
        $message .= 'Content-Transfer-Encoding: 7bit'. $eol. $eol;

        $message .= 'Content-Description: PGP/MIME Versions Identification'. $eol. $eol;
        $message .= 'Version: 1'. $eol. $eol;


        $message .= $eol . $eol . "--$boundary" . $eol;


        if($body['text']!==""){

            $message .= 'Content-type: application/octet-stream; name="encrypted.asc"'.$eol;
            $message .= 'Content-Transfer-Encoding: 7bit'.$eol;
            $message .= 'Content-ID: <0>'.$eol;
            $message .= 'Content-Disposition: inline; filename="encrypted.asc"'.$eol.$eol;


            $message .= $body['text'].$eol.$eol;

           // $message .=$eol.$eol."--$boundary".$eol;
        }

       /* if($body['text']!==""){
            $message .= "Content-type: text/plain;charset=utf-8".$eol.$eol;
            $message .= $body['text'].$eol.$eol;
            $message .=$eol.$eol."--$boundary".$eol;
        }*/

            $message .= "--$boundary--";


/*        print_r($headers);
        print_r($message);
        Yii::app()->end();*/

        $SavingUserDataV2 = new SavingUserDataV2();
        if (mail(null, $subject, $message, $headers, "-f" . $SavingUserDataV2->extract_email_address($from)[0])){
            return true;
        }else{
            return false;
        }


        //} else
        //	return true;
    }


	/**
	 * @param $to
	 * @param $cc
	 * @param $from
	 * @param $subject
	 * @param $body
	 * @return bool
	 */
	public function sendEmail($to, $cc, $from, $subject, $body)
	{

		foreach ($to as $index => $row) {
			$to[$index] = base64_decode($row);
		}
		foreach ($cc as $index => $row) {
			$cc[$index] = base64_decode($row);
		}
		$subject = base64_decode($subject);
		$from = base64_decode($from);
		$body['text'] = base64_decode($body['text']);
		$body['html'] = base64_decode($body['html']);


		//if (Yii::app()->params['production']) {


		$boundary = uniqid('np');

		$eol = "\r\n";

		$headers = "MIME-Version: 1.0" . $eol;
		$headers .= "From: " . $from . $eol . "Reply-To: " . $from . $eol;

		$headers .= "To: " . implode(", ", $to) . $eol;
		if (count($cc) > 0) {
			$headers .= "CC: " . implode(", ", $cc) . $eol;
		}


		$headers .= "Content-Type: multipart/alternative; boundary=$boundary" . $eol . $eol;

		$message = "This is a MIME encoded message.";
		$message .= $eol . $eol . "--$boundary" . $eol;

		if($body['text']!==""){
			$message .= "Content-type: text/plain;charset=utf-8".$eol.$eol;
			$message .= $body['text'].$eol.$eol;
			$message .=$eol.$eol."--$boundary".$eol;
		}


		if($body['html']!==""){
			$message .= "Content-type: text/html;charset=utf-8" . $eol . $eol;
			$message .= $body['html'] . $eol . $eol;
			$message .= $eol . $eol . "--$boundary--";
		}else{
			$message .= "Content-type: text/html;charset=utf-8" . $eol . $eol;
			$message .= $body['text'] . $eol . $eol;
			$message .= $eol . $eol . "--$boundary--";
		}


		/*print_r($headers);
		print_r($message);
		Yii::app()->end();*/

        if(count($to)>0){
            $tos=$to[0];
        }else{
            $tos="dummy@scryptmail.com";
        }

		$SavingUserDataV2 = new SavingUserDataV2();
		if (mail($tos, $subject, $message, $headers, "-f" . $SavingUserDataV2->extract_email_address($from)[0])){
			return true;
		}else{
			return false;
		}


		//} else
		//	return true;
	}


	public function makeModKey($size)
	{
		return openssl_random_pseudo_bytes($size);
	}

	public function toAes($key, $text)
	{
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		$iv = openssl_random_pseudo_bytes($iv_size);

		$encryptionMethod = "aes-256-cbc";

		$encryptedMessage = openssl_encrypt($text, $encryptionMethod, $key, 0, $iv);

		return base64_encode($iv) . ';' . $encryptedMessage;

	}

}

