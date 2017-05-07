<?php

class EmailparseCommand extends CFormModel
{
	public $mails;


	public function rules()
	{
		return array(
			array('mails', 'email', 'allowEmpty' => false),
		);
	}

	public function receiveNewEmailV1($emailKeyWithName, $draft)
	{
		$mailKey = $emailKeyWithName['mailKey'];

		$emailPreObj['to'] = $draft['meta']['to'];

		$emailPreObj['from'] = $draft['meta']['from'];
		$emailPreObj['subj'] = $draft['meta']['subject'];


		$emailPreObj['body']['text'] = $draft['body']['text'];
		$emailPreObj['body']['html'] = $draft['body']['html'];


		$emailPreObj['rawHeader'] = $draft['rawHeader'];

		$emailPreObj['meta']['subject'] = $draft['meta']['subject'];
		$emailPreObj['meta']['from'] = $draft['meta']['from'];


		$emailPreObj['meta']['body'] = $draft['meta']['body'];
		$emailPreObj['meta']['timeRcvd'] = $draft['meta']['timeRcvd'];
		$emailPreObj['meta']['timeSent'] = $draft['meta']['timeSent'];

		$emailPreObj['meta']['opened'] = $draft['meta']['opened'];
		$emailPreObj['meta']['pinEnabled'] = $draft['meta']['pinEnabled'];
		$emailPreObj['meta']['type'] = $draft['meta']['type'];
		$emailPreObj['meta']['pin'] = $draft['meta']['pin'];
		$emailPreObj['meta']['status'] = $draft['meta']['status'];

		$emailPreObj['meta']['to'] = $draft['meta']['to'];
		$emailPreObj['meta']['cc'] = $draft['meta']['toCC'];

		$emailPreObj['meta']['modKey'] = $draft['modKey'];

		$emailPreObj['modKey'] = $draft['modKey'];


		/*
				$emailPreObj['from'] = base64_encode($emailPreObj['from']);
				$emailPreObj['subj'] = base64_encode($emailPreObj['subj']);

				$emailPreObj['body']['text'] = base64_encode($emailPreObj['body']['text']);
				$emailPreObj['body']['html'] = base64_encode($emailPreObj['body']['html']);
				$emailPreObj['meta']['subject'] = base64_encode($emailPreObj['meta']['subject']);
				$emailPreObj['meta']['from'] = base64_encode($emailPreObj['meta']['from']);
				$emailPreObj['meta']['body'] = base64_encode($emailPreObj['meta']['body']);
				$emailPreObj['rawHeader'] = base64_encode($emailPreObj['rawHeader']);

				$emailPreObj['to'] = array_map('base64_encode', $emailPreObj['to']);
				$emailPreObj['meta']['to'] = array_map('base64_encode', $emailPreObj['meta']['to']);
				$emailPreObj['meta']['cc'] =array_map('base64_encode', $emailPreObj['meta']['cc']);

				*/

		$emailPreObj['attachment'] = $draft['attachment'];
		$emailPreObj['meta']['version'] = 15;
		$emailPreObj['meta']['attachment'] = $draft['meta']['attachment'];


		$key = EmailparseCommand::makeModKey(32);

		$body = EmailparseCommand::toAes($key, json_encode($emailPreObj));
		$meta = EmailparseCommand::toAes($key, json_encode($emailPreObj['meta']));

		$response['mail'] = $body;
		$response['meta'] = $meta;

		$response['modKey'] = $emailPreObj['meta']['modKey'];
		$response['key'] = base64_encode(EmailparseCommand::encrypt($mailKey, $key));
		$response['seedRcpnt'] = base64_encode($emailKeyWithName['email']);
		return $response;
	}


	public function receiveNewEmailV2($v2Keys, $draft)
	{
		//print_r($v2Keys);
		$rr = Yii::app()->basePath . '/pgps/' . hash('sha256', openssl_random_pseudo_bytes(16));
		mkdir($rr, 0777);
		putenv("GNUPGHOME=$rr");

		$key = EmailparseCommand::makeModKey(32);
		$email = EmailparseCommand::toAes($key, json_encode($draft));

		$toCCmeta = array(
			'attachment' => $draft['meta']['attachment'],
			'to' => $draft['meta']['to'],
			'toCC' => $draft['meta']['toCC'],
			'from' => $draft['meta']['from'],
			'subject' => $draft['meta']['subject'],
			'body' => $draft['meta']['body'],
			'fromExtra' => '',
			'version' => 2,
			'en' => 0,

			'timeSent' => $draft['meta']['timeSent'],
			'pin' => '',

			'modKey' => $draft['modKey'],
			'type' => $draft['meta']['type'], //received
			'status' => $draft['meta']['status'],
			'emailHash' => hash('sha512', json_encode($draft)),// app.transform.SHA512(JSON.stringify(emailtoCC)),
			'emailKey' => base64_encode($key)//app.transform.to64bin(key)
		);

		//print_r($toCCmeta);


		$gpg = new gnupg();
		$gpg->seterrormode(gnupg::ERROR_EXCEPTION);

		//try {
		foreach ($v2Keys as $i => $key64) {
			$key = $gpg->import(base64_decode($key64));
			$gpg->addencryptkey($key['fingerprint']);

			//print_r($key);
		}

		$meta64 = base64_encode(json_encode($toCCmeta));
		$response['email'] = $email;
		$metaEnc = $gpg->encrypt($meta64);

		//print_r(hash('sha256', base64_decode($key64)));

		$response['meta'] = base64_encode($metaEnc);
		//$enc = $gpg -> encrypt($draft64);
		//print_r($response);


		//} catch (Exception $e) {
		//	return false;
		//}

		exec("rm -rf {$rr}");

		return $response;
	}

    //run against service spam database, drop email if its very bad
    public function checkFilterRulesUniversal($sender)
    {
        $domain64=base64_encode(explode('@', $sender)[1]);
        $sender64=base64_encode($sender);

        $mngData[]=array("txt"=>$domain64);
        $mngData[]=array("txt"=>$sender64);

        $criteria=array('$or'=>$mngData);

        if($filterResult=Yii::app()->mongo->findAll('universalBlackList',$criteria,array(),null,array('mF'=>1))){

            foreach ($filterResult as $index => $fRule) {
                //    print_r($fRule);
                //if email Match
                if($fRule['mF']===1){
                    if($fRule['dest']===0){
                        return false;
                    }else{
                        return true;
                    }
                }
                //if domain match
                if($fRule['mF']===3){
                    if($fRule['dest']===0){
                        return false;
                    }else{
                        return true;
                    }
                }
            }
            return true;
        }else{
            return true;
        }
        //  print_r($filterResult);

    }

    //run against each recipient and return true if pass, false if need to be dropped
    public function checkFilterRules($sender,$recipient)
    {
        $domain64=base64_encode(explode('@', $sender)[1]);
        $sender64=base64_encode($sender);

        $mngData[]=array("txt"=>$domain64);
        $mngData[]=array("txt"=>$sender64);
        $score=5;

        $criteria=array('userId'=>$recipient,'$or'=>$mngData);

        if($filterResult=Yii::app()->mongo->findAll('blackList',$criteria,array(),null,array('mF'=>1))){

            foreach ($filterResult as $index => $fRule) {
                //if email Match
                if($fRule['mF']===1){
                    if($fRule['dest']===0){
                        $score=0;
                    }else{
                        $score=10;
                    }
                }
                if($fRule['mF']===3){
                    if($fRule['dest']===0){
                        $score=0;
                    }else{
                        $score=10;
                    }
                }
            }
        }

        if($score===5){
            $criteria=array('userId'=>$recipient,"txt"=>'Kg==');
            if($filterResult=Yii::app()->mongo->findOne('blackList',$criteria)){
                if($filterResult['dest']===0){
                    $score=0;
                }else{
                    $score=5;
                }
            }
        }
        // Yii::app()->end();
        return $score;
      //  print_r($filterResult);

    }
    public function runThroughFilter($sender,$recipients)
    {
        if(isset($recipients) && count($recipients)>0){
            foreach ($recipients as $index => $addressData) {

                $filterScore=$this->checkFilterRules($sender,$addressData['userId']);
               if($filterScore===0){
                    unset($recipients[$index]);
                }else if($filterScore===5){
                   //rule, dont exist. we will apply universal filter
                   if(!$this->checkFilterRulesUniversal($sender)){
                       unset($recipients[$index]);
                   }
               }
            }
        }

        return $recipients;
    }

	public function run($args)
	{
		//try {

			if (Yii::app()->params['production']) {
				//$rawEmail = fopen("php://stdin", "r"); //production

				$fd = fopen("php://stdin", "r");
				$rawEmail = "";
				while (!feof($fd)) {
					$rawEmail .= fread($fd, 1024);
				}
				fclose($fd);

			} else {
				$path = Yii::app()->basePath . '/extensions/m0016';
				$rawEmail = file_get_contents($path); //test
			}

			//save failed email in db for future analysis
			//

			$message = Yii::app()->EmailParser->getEmailFull($rawEmail);

		//Yii::app()->end();
			$headers = $message['header'];
			$body = $message['body'];
			$attachmentObj = $message['attachment'];

			$recipients = $headers['to'];
			$recipients = array_merge($recipients, $headers['cc']);
			$recipients = array_merge($recipients, $headers['bcc']);


			//check if all rcpnts are emails

			$grecip = array_filter($recipients,
				function ($var) {
				return (isset(Yii::app()->SavingUserDataV2->extract_email_address($var)[0]));
			});


			if (is_array($grecip) && count($grecip) > 0) {

				foreach ($grecip as $i => $emailString) {
					$em = strtolower(Yii::app()->SavingUserDataV2->extract_email_address($emailString)[0]);
					$dom = hash('sha512', explode('@', $em)[1]);

					$hash = hash('sha512', $em);
					$param[":addressHash_$i"] = $hash;

					$mngData[]=array('addressHash'=>$hash,'active'=>1);

					$email[$hash]['emailString'] = $emailString;
					$email[$hash]['email'] = $em;
					$email[$hash]['domain'] = $dom;
					$email[$hash]['dest'] = $dom;

					$verifyDomain[":domains_$i"] = $dom;
				}

				//print_r($mngData);

				$paramDomain = implode(array_keys($verifyDomain), ',');

				if ($verifiedDomains = Yii::app()->db->createCommand("SELECT domain,shaDomain FROM virtual_domains WHERE shaDomain IN ($paramDomain) AND mxRec=1")->queryAll(true, $verifyDomain)) {
					foreach ($verifiedDomains as $row) {
						$verifiedEmailsArray[] = strtolower($row['shaDomain']);
					}




					$mngDataAgregate=array('$or'=>$mngData);

					if($mailKeysNew=Yii::app()->mongo->findAll('addresses',$mngDataAgregate)){
						//print_r($mailKeysNew);
						foreach($mailKeysNew as $mNhash){
							$testHash[]=$mNhash['addressHash'];
						}


						foreach($param as $pIndex=>$pNhash){
							if(in_array($pNhash,$testHash)){
								unset($param[$pIndex]);
							}

						}
					}

					$str = implode(array_keys($param), ',');

					if(count($mailKeysNew)>0){

						if(isset($mailKeys) && count($mailKeys)>0){
						$mailKeys=array_merge($mailKeys,$mailKeysNew);
						}else{
							$mailKeys=$mailKeysNew;
						}


                    }
                    if(isset($mailKeys) && count($mailKeys)>0){
                        $strData = EmailparseCommand::createDraft($rawEmail, $headers, $body, $attachmentObj);
                        $sender=strtolower(Yii::app()->SavingUserDataV2->extract_email_address(base64_decode($strData['draft']['meta']['from']))[0]);
                        $mailKeys=$this->runThroughFilter($sender,$mailKeys);
                    }


					if(isset($mailKeys) && count($mailKeys)>0){
					//	print_r($mailKeys);

					//	Yii::app()->end();


						//$strData = EmailparseCommand::createDraft($rawEmail, $headers, $body, $attachmentObj);

						$draft = $strData['draft'];
						$attach = $strData['attach'];

						$inTextEmails = array(
							'toCCrcpt' => array(),
							'toCCrcptV1' => array(),
							'bccRcptV1' => array(),
							'bccRcpt' => array(),


							'attachments' => $attach,
							'aSize' => $draft['meta']['emailSize'],
							'modKey' => hash('sha512', $draft['meta']['modKey']),
							'refId' => 0,
							'sender' => $draft['meta']['from']

						);


						foreach ($mailKeys as $i => $emData) {
							$emailKeyWithName = $emData;
							$emailKeyWithName['name'] = $email[$emData['addressHash']]['emailString'];
							$emailKeyWithName['email'] = $email[$emData['addressHash']]['email'];
							$emailKeyWithName['domain'] = $email[$emData['addressHash']]['domain'];

							//print_r($email[$emData['addressHash']]);


							if (in_array($emailKeyWithName['domain'], $verifiedEmailsArray)) {
								if ((int) $emailKeyWithName['v'] === 1) {
									$inTextEmails['toCCrcptV1'][] = EmailparseCommand::receiveNewEmailV1($emailKeyWithName, $draft);
								}

								if ((int) $emailKeyWithName['v'] === 2 && (int) $emailKeyWithName['active'] === 1) {

									$inTextEmails['toCCrcpt']['recipients'][] = base64_encode($emailKeyWithName['email']);
									$v2Keys[] = $emailKeyWithName['mailKey'];
								}
							}


						}

						if (isset($v2Keys) && is_array($v2Keys)) {
							$inTextEmails['toCCrcpt'] = array_merge($inTextEmails['toCCrcpt'], EmailparseCommand::receiveNewEmailV2($v2Keys, $draft));

						}

						//print_r($inTextEmails);
						//Yii::app()->end();

						unset($param);

						$param[':email'] = json_encode($inTextEmails);
						//$param[':pKey']=$encryptedEmail['mailKey'];

						$param[':modKey'] = $inTextEmails['modKey'];
						$param[':refId'] = $inTextEmails['refId'];
						$param[':destination'] = 4;
						//print_r($headers);
						//Yii::log(CVarDumper::dumpAsString($rawEmail), 'vardump', 'system.web.CController22');
						//Yii::app()->end();

						$param[':messageId'] = hash('sha512',json_encode($headers['Message-ID']).json_encode($recipients));

						//print_r($message['header']);


						if (Yii::app()->db->createCommand('INSERT IGNORE INTO mail2sent (email,refId,modKey,destination,messageId) VALUES(:email,:refId,:modKey,:destination,:messageId)')->execute($param)) {
							$result['response'] = 'success';
						}

					}

				}
			}

		Yii::app()->end();

	}

	public function createDraft($rawEmail, $headers, $body, $attachmentObj)
	{

		$from = trim(strip_tags($headers['from']));



		if(!isset(Yii::app()->SavingUserDataV2->extract_email_address($headers['from'])[0])){
			$draft['meta']['from'] =$from;
		}else{
			$draft['meta']['from'] = ($from != Yii::app()->SavingUserDataV2->extract_email_address($headers['from'])[0]) ? $from . " <" . Yii::app()->SavingUserDataV2->extract_email_address($headers['from'])[0] . ">" : Yii::app()->SavingUserDataV2->extract_email_address($headers['from'])[0];
		}


		$draft['meta']['from'] = base64_encode($draft['meta']['from']);

		$draft['meta']['to'] = array_map('base64_encode', $headers['to']);
		$draft['meta']['toCC'] = array_map('base64_encode', $headers['cc']);

		$draft['meta']['attachment'] = 0;


		$text = isset($body['text']) ? strip_tags($body['text']) : '';
		$html = isset($body['html']) ? $body['html'] : '';
		$metb = ($text != '') ? $text : $html;
		$draft['meta']['body'] = base64_encode(mb_substr(strip_tags($metb), 0, 50));

		$draft['meta']['subject'] = base64_encode(mb_substr(htmlspecialchars($headers['subject'], ENT_QUOTES, "UTF-8"), 0, 150));


		$draft['meta']['opened'] = false;
		$draft['meta']['pin'] = '';
		$draft['meta']['pinEnabled'] = false;
		$draft['meta']['status'] = "normal";

		$draft['rawHeader'] = base64_encode(substr(Yii::app()->EmailParser->getHeader($rawEmail), 0, 10000));

		$draft['meta']['timeRcvd'] = time();
		$draft['meta']['timeSent'] = strtotime($headers['sent']);

		$draft['meta']['type'] = 1;
		$draft['meta']['version'] = 2;


		$draft['body']['text'] = base64_encode($text);
		$draft['body']['html'] = base64_encode($html);

		//draft['meta']['signatureOn']=true;

		$modKey = bin2hex(EmailparseCommand::makeModKey(16));
		$draft['meta']['modKey'] = $modKey;
		$draft['modKey'] = $modKey;

		$draft['meta']['emailSize'] = 0;
		$draft['attachment'] = array();

		$recipients = $headers['to'];
		$recipients = array_merge($recipients, $headers['cc']);
		$recipients = array_merge($recipients, $headers['bcc']);

		$attach = array();
		if (isset($attachmentObj) && is_array($attachmentObj) && count($attachmentObj) > 0) {
			foreach ($attachmentObj as $k => $file) {
				if ($file['content'] != "Version: 1" && $file['type'] != "application/pgp-encrypted") {
					$key = EmailparseCommand::makeModKey(32);

					if ($encryptedData = FileWorkerV2::encryptFile($file['content'], $key, null)) {
						$fileData['data'] = $encryptedData;
						$fileData['expire'] = strtotime('+1 year', time());
						$fileData['fName']=$file['name'];

						$fileData['modKey'] = bin2hex(EmailparseCommand::makeModKey(16));
						$size = strlen($file['content']);
						$draft['meta']['emailSize'] += $size;

						if (mb_stripos($file['content'], '-----BEGIN PGP MESSAGE-----') !== false && mb_stripos($file['content'], '-----END PGP MESSAGE-----') !== false) {
							$is_pgp = true;
						} else {
							$is_pgp = false;
						}

						if (mb_stripos($file['content'], '-----BEGIN PGP SIGNATURE-----') !== false && mb_stripos($file['content'], '-----END PGP SIGNATURE-----') !== false) {
							$is_pgpSign = true;
						} else {
							$is_pgpSign = false;
						}


						if ($fileName = FileWorkerV2::createNewAttachment($fileData,$headers['Message-ID'].json_encode($recipients))) {

							$draft['attachment'][base64_encode($file['name'])] =
								array(
									'name' => base64_encode($file['name']),
									'key' => base64_encode($key),
									'type' => base64_encode($file['type']),
									'fileName' => $fileName,
									'size' => base64_encode($size),
									'base64' => true,
									'isPgp' => $is_pgp,
									'modKey' => $fileData['modKey']
								);
							$attach[$fileName] = $fileData['modKey'];

						} else {
							//todo save into fail2deliver
							//and stop execution
						}


					}
				}

			}
			$draft['meta']['attachment'] = 1;
		} else {
			$draft['meta']['attachment'] = 0;
		}

		//print_r($draft);
		//Yii::app()->end();

		$resp['draft'] = $draft;
		$resp['attach'] = $attach;

		return $resp;
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

	public function encrypt($key, $data)
	{
		openssl_public_encrypt($data, $encrypted, base64_decode($key), OPENSSL_PKCS1_OAEP_PADDING);
		//$data = bin2hex($encrypted);

		return $encrypted;
	}

}

