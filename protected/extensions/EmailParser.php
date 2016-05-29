<?php
/**
 * Sergei Krutov
 * Date: 3/30/15
 * Time: 9:58 PM
 */

class EmailParser extends CApplicationComponent
{

public $Parser;

	public function init()
	{
		include 'EmailParserExtension/Parser.php';
		include 'EmailParserExtension/Attachment.php';
		include 'EmailParserExtension/Exception.php';

		$this->Parser = new Parser();
		//require("libs/nusoap-0.9.5/lib/nusoap.php");
		return parent::init();


	}

	public function getResults($rawEmail)
	{
		$Parser = new Parser();
		//$Parser->setStream($rawEmail);
		$Parser->setText($rawEmail);
// We can get all the necessary data
		$email['to']=$Parser->getHeader('to');
		$email['cc']=$Parser->getHeader('cc');
		$email['bcc']=$Parser->getHeader('bcc');
		$email['fwd']=$Parser->getHeader('x-forwarded-to');
		$email['res_to']=$Parser->getHeader('resent-to');
		$email['from']=$Parser->getHeader('from');
		$email['subject']=$Parser->getHeader('subject');
		$email['received']=date('Y-m-d H:i:s');
		$email['sent']=date('Y-m-d H:i:s',strtotime($Parser->getHeader('date')));
		$email['text']=$Parser->getMessageBody('text');
		$email['html']=$Parser->getMessageBody('html');
		$email['htmlEmbedded']=$Parser->getMessageBody('htmlEmbedded');//HTML Body included data
// and the attachments also
		//$attach_dir = Yii::app()->basePath.'/extensions/';
		$email['attachmentObj']=$Parser->saveAttachments();
		//$Parser->saveAttachments($attach_dir);
		return $email;

	}


	public function getEmailFull($rawEmail)
	{
		$Parser = new Parser();
		$Parser->setText($rawEmail);

		$func = function ($data)
		{
			$resp = imap_utf8(trim($data));

			//print_r($resp);

			if(preg_match("/=\?([^?]+)\?/", $resp,$enc)) {
				//print_r($enc);
				$resp = iconv_mime_decode($data, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, $enc[1]);
			}


			try{
				if(json_encode($resp) == 'null') {
					$resp = utf8_encode($resp);
				}

			} catch (Exception $e) {
				$resp = utf8_encode($resp);
			}

			return $resp;
		};

		$emHeader['to']=$Parser->getHeader('to');
		$emHeader['to']=array_values(array_filter(explode(",",$emHeader['to']), 'strlen'));



		$emHeader['cc']=$Parser->getHeader('cc');
		$emHeader['Message-ID']=$Parser->getHeader('message-id');
		$emHeader['cc']=array_values(array_filter(explode(",",$emHeader['cc']), 'strlen'));

		$emHeader['bcc']=$Parser->getHeader('bcc');
		$emHeader['bcc']=array_values(array_filter(explode(",",$emHeader['bcc']), 'strlen'));

		$emHeader['fwd']=$Parser->getHeader('x-forwarded-to');
		$emHeader['fwd']=array_values(array_filter(explode(",",$emHeader['fwd']), 'strlen'));

		$emHeader['res_to']=$Parser->getHeader('resent-to');
		$emHeader['res_to']=array_values(array_filter(explode(",",$emHeader['res_to']), 'strlen'));

		$emHeader['reply-to']=$Parser->getHeader('reply-to');
		$emHeader['return-path']=$Parser->getHeader('return-path');

		//$emHeader['from']=$Parser->getHeader('from');
		$emHeader['from']=$func($Parser->getHeader('from'));
		//print_r($func($Parser->getHeader('from')));
		//$emHeader['subject']=$func($Parser->getHeader('subject'));

		$emHeader['received']=$Parser->getHeader('received');

		$emHeader['to']=array_merge($emHeader['to'],$emHeader['res_to']);
		$emHeader['to']=array_merge($emHeader['to'],$emHeader['fwd']);



		//print_r($emHeader);
		//for bcc in received header
		$pattern = "/.\bfor\b.*$/m";
		$emHeader['possible-recipient']=array();
		if(!is_array($emHeader['received'])){
			$emHeader['received']=array($emHeader['received']);
		}

			$SavingUserDataV2 = new SavingUserDataV2();

			foreach($emHeader['received'] as $row){
				preg_match($pattern, $row, $matches);
				if(!empty($matches)){
					//$email['possible-recipient'][]=$matches;
					$emHeader['possible-recipient']=array_merge($emHeader['possible-recipient'],$SavingUserDataV2->extract_email_address($matches[0]));
				}

			}
			//$email['possible-recipient']=array_values($email['possible-recipient']);
			$emHeader['possible-recipient']=array_unique($emHeader['possible-recipient']);
		//}

		//$email['x-apparently-to']=$Parser->getHeader('x-apparently-to');
		$chk=$Parser->getHeader('x-apparently-to');
		if(isset($chk) && !empty($chk)){
			$emHeader['to'][]=$chk;
		}


		//remove dups if same recip present in to,cc or


		//print_r($func);
		//print_r($emHeader['possible-recipient']);
		//Yii::app()->end();

		if(is_array($emHeader['possible-recipient'])){

			$in_arrayi=function ($needle, $haystack) {
				$chk=false;
				$SavingUserDataV2 = new SavingUserDataV2();
				foreach($haystack as $krow){
					$needl=$SavingUserDataV2->extract_email_address($krow);

					if(empty($needl)){
						return true;
					}else if(strtolower($needl[0])===strtolower($needle)){
						$chk=true;
					}else{
						$chk=false;
					}
					if($chk){
						return true;
					}
				}
				return false;
			};


			foreach($emHeader['possible-recipient'] as $index=>$pEmail){
				if(
					$in_arrayi($pEmail,$emHeader['to'])
					||
					$in_arrayi($pEmail,$emHeader['cc'])
					||
					$in_arrayi($pEmail,$emHeader['bcc'])
				)
				{
					unset($emHeader['possible-recipient'][$index]);
				}

				$emHeader['to']=array_merge($emHeader['to'],$emHeader['possible-recipient']);
			}

		}
		$testDups=array();
		if(is_array($emHeader['to']) && count($emHeader['to'])){
			$SavingUserDataV2 = new SavingUserDataV2();
			foreach($emHeader['to'] as $index=>$row){
				$em=$SavingUserDataV2->extract_email_address($row);
				if(isset($em[0])){
					$em=strtolower($SavingUserDataV2->extract_email_address($row)[0]);

					if(in_array($em,$testDups)){
						unset($emHeader['to'][$index]);
					}else{
						$testDups[]=$em;
					}
				}else{
					unset($emHeader['to'][$index]);
				}


			}
		}



		$testDups=array();

		if(is_array($emHeader['cc']) && count($emHeader['cc'])){
			$SavingUserDataV2 = new SavingUserDataV2();

			foreach($emHeader['cc'] as $index=>$row){

				$em=$SavingUserDataV2->extract_email_address($row);
				if(isset($em[0])){
					$em=strtolower($SavingUserDataV2->extract_email_address($row)[0]);

					if(in_array($em,$testDups)){
						unset($emHeader['cc'][$index]);
					}else{
						$testDups[]=$em;
					}
				}else{
					unset($emHeader['cc'][$index]);
				}

			}
		}


		//Yii::app()->end();

		unset($emHeader['possible-recipient'],$emHeader['res_to'],$emHeader['fwd']);

		$emHeader['subject']=$func($Parser->getHeader('subject'));

		$emHeader['received']=date('Y-m-d H:i:s');
		$emHeader['sent']=date('Y-m-d H:i:s',strtotime($Parser->getHeader('date')));


		$email['text']=$Parser->getMessageBody('text');
		$email['html']=$Parser->getMessageBody('html');
		//$email['htmlEmbedded']=$Parser->getMessageBody('htmlEmbedded');//HTML Body included data

		$attachment=$Parser->saveAttachments();

		if(count($emHeader['to'])>0){
			foreach($emHeader['to'] as $i=>$k){
				$emHeader['to'][$i]=$func($k);
			}
		}
		if(count($emHeader['cc'])>0){
			foreach($emHeader['cc'] as $i=>$k){
				$emHeader['cc'][$i]=$func($k);
			}
		}

		$emailObj['header']=$emHeader;
		$emailObj['body']=$email;
		$emailObj['attachment']=$attachment;

		//print_r($emHeader);

		//print_r($emHeader);
		//Yii::app()->end();

		return $emailObj;

	}


	public function getHeader($email)
	{

			$pars=$this->Parser;


			$pars->setText($email);

			$headerPos=$pars->getHeaderAll();


			return substr($email,$headerPos['start'],$headerPos['end']);
		}


}