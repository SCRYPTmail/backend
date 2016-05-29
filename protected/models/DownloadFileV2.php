<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */
class downloadFile extends CFormModel
{
	public $userToken;

	public $fileName,$modKey;

	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),

			//downloadFile
			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32}$/i", 'allowEmpty' => false, 'on' => 'downloadFile','message'=>'fld2upd'),
			array('fileName', 'match', 'pattern' => "/^[a-z0-9\d]{128}$/i", 'allowEmpty' => false, 'on' => 'downloadFile','message'=>'fld2upd'),

		);
	}

	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}

	public function downloadFile()
	{

		$fileHash=$this->fileName;
		$key=hex2bin(substr($fileHash,0,64));
		$fileName=substr($fileHash,64);

		$this->fileData=str_ireplace('name/','',$this->fileData);

		$fileData=explode('-',$this->fileData);

		$name=base64_decode($fileData[0]);
		$type=base64_decode($fileData[1]);


		if($file=FileWorks::readFile($fileName)){
			try{

				$data = $file;
				$iv = hex2bin(substr($data, 0, 32));
				$encrypted = substr($data, 32);

				$encryptionMethod = "aes-256-cbc";
				$g=openssl_decrypt($encrypted, $encryptionMethod, $key, 0, $iv);

				header("Cache-Control: public");
				header("Content-Description: File Transfer");
				header("Content-Disposition: attachment; filename=".$name);
				header("Content-Type: ".$type);
				header("Content-Transfer-Encoding: binary");
				echo base64_decode($g);

			} catch (Exception $e) {
				echo '{"file":"file not found1"}';
			}

		}else{
			echo 'File you requested is no longer available.';

		}

	}

	//old=====================================================

	public function download()
	{
		$fileHash=$this->fileHash;
		$key=hex2bin(substr($fileHash,0,64));
		$fileName=substr($fileHash,64);

		$this->fileData=str_ireplace('name/','',$this->fileData);

		$fileData=explode('-',$this->fileData);

		$name=base64_decode($fileData[0]);
		$type=base64_decode($fileData[1]);


			if($file=FileWorks::readFile($fileName)){
				try{

				$data = $file;
				$iv = hex2bin(substr($data, 0, 32));
				$encrypted = substr($data, 32);

					$encryptionMethod = "aes-256-cbc";
					$g=openssl_decrypt($encrypted, $encryptionMethod, $key, 0, $iv);

					header("Cache-Control: public");
					header("Content-Description: File Transfer");
					header("Content-Disposition: attachment; filename=".$name);
					header("Content-Type: ".$type);
					header("Content-Transfer-Encoding: binary");
					echo base64_decode($g);

				} catch (Exception $e) {
					echo '{"file":"file not found1"}';
				}

				}else
			echo 'File you requested is no longer available.';

	}
}