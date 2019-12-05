<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class FileWV2 extends CFormModel
{

	public $file,$fileName,$fileId,$destId;


	public function rules()
	{
		return array(
			// deleteEmailUnreg
			//array('messageId', 'match', 'pattern' => "/^[a-zA-Z0-9\d]+$/i", 'allowEmpty' => false, 'on' => 'deleteEmailUnreg','message'=>'fld2upd'),
			//array('messageId','length', 'max'=>128,'allowEmpty' => false,'on'=>'deleteEmailUnreg','message'=>'fld2upd'),

			//array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'deleteEmailUnreg','message'=>'fld2upd'),

			//	array('mailHash', 'numerical','integerOnly'=>true,'allowEmpty'=>true),
		);
	}

    public function getToken()
    {

        $xmlpost  = array(
            'apikey'=> Yii::app()->params['apikey'],
            'response_type'=>'cloud_iam',
            'grant_type'=>'urn:ibm:params:oauth:grant-type:apikey'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Yii::app()->params['softToken']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded','Accept:application/json',
        ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($xmlpost));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response  = curl_exec($ch);
        $fd=json_decode($response);
        return $fd->access_token;
        curl_close($ch);

    }

    public function makeCopyWithMeta($fileId,$destId)
    {

        $token=FileWV2::getToken();
        $heads[]=('Content-type: application/octet-stream');
        $heads[]=('x-amz-copy-source: '.'/'.Yii::app()->params['folder'].'/'.$fileId);

        if(isset($token)){

            $headers = array();
            $headers[] = 'Authorization: Bearer '.$token;
            $h=array_merge($headers,$heads);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, Yii::app()->params['host'].'/'.Yii::app()->params['folder'].'/'.$destId);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

            curl_setopt($ch, CURLOPT_HTTPHEADER, $h);


            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                curl_close($ch);
                return false;
            }else{
                curl_close($ch);
                return true;
            }



        }else{
            return false;
        }

    }
    public function saveFile($fileId,$file,$expire=null)
    {
        $heads[]=('Content-type: application/octet-stream');
        $token=FileWV2::getToken();

        if(isset($token)){

            $headers = array();
            $headers[] = 'Authorization: Bearer '.$token;
            $h=array_merge($headers,$heads);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, Yii::app()->params['host'].'/'.Yii::app()->params['folder'].'/'.$fileId);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

            curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $file);

            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                curl_close($ch);
               return false;
            }else{
                curl_close($ch);
                return true;
            }



        }else{
            return false;
        }


    }
    public function deleteFile($fileId)
    {
        $token=FileWV2::getToken();

        if(isset($token)){

            $headers = array();
            $headers[] = 'Authorization: Bearer '.$token;


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, Yii::app()->params['host'].'/'.Yii::app()->params['folder'].'/'.$fileId);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                curl_close($ch);
                //return false;
            }else{
                curl_close($ch);
               // return true;
            }

           // Yii::app()->end();
            return 1;
        }
    }
    public function ifExt($fileId)
    {
        $token=FileWV2::getToken();

        if(isset($token)){

            $headers = array();
            $headers[] = 'Authorization: Bearer '.$token;


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, Yii::app()->params['host'].'/'.Yii::app()->params['folder'].'/'.$fileId);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_HEADER, 1);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $res = curl_exec($ch);

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

            $header = substr($res, 0, $header_size);

            foreach (explode("\r\n", $header) as $i => $line)
                if ($i === 0)
                    $resul['headers']['http_code'] = $line;
                else
                {
                    list ($key, $value) = explode(': ', $line);
                    $resul['headers'][$key] = $value;
                }

            if ($resul['headers']["http_code"] !== 'HTTP/1.1 200 OK') {
                $result="not found" ;
            }else{
                $result = "found";
            }

            if (curl_errno($ch)) {
                curl_close($ch);
                //return false;
            }else{
                curl_close($ch);
                // return true;
            }
            return $result;
        }

    }

    public function getSize($fileId)
    {
        $token=FileWV2::getToken();

        if(isset($token)){
            if(FileWV2::ifExt($fileId)!=='not found'){

                $fnamed=$fileId;

            }else{
                if(FileWV2::ifExt('del_'.$fileId)!='not found'){
                    $fnamed='del_'.$fileId;
                }
            }


            $headers = array();
            $headers[] = 'Authorization: Bearer '.$token;


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, Yii::app()->params['host'].'/'.Yii::app()->params['folder'].'/'.$fnamed);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_HEADER, 1);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $res = curl_exec($ch);

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

            $header = substr($res, 0, $header_size);

            foreach (explode("\r\n", $header) as $i => $line)
                if ($i === 0)
                    $resul['headers']['http_code'] = $line;
                else
                {
                    list ($key, $value) = explode(': ', $line);
                    $resul['headers'][$key] = $value;
                }


            if (curl_errno($ch)) {
                curl_close($ch);
                //return false;
            }else{
                curl_close($ch);
                // return true;
            }
            return $resul['headers']['Content-Length'];
        }

    }

	public function readFile($fileId)
	{
        $token=FileWV2::getToken();

        if(isset($token)){

            $headers = array();
            $headers[] = 'Authorization: Bearer '.$token;


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, Yii::app()->params['host'].'/'.Yii::app()->params['folder'].'/'.$fileId);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $res = curl_exec($ch);

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

            $header = substr($res, 0, $header_size);

            foreach (explode("\r\n", $header) as $i => $line)
                if ($i === 0)
                    $resul['headers']['http_code'] = $line;
                else
                {
                    list ($key, $value) = explode(': ', $line);
                    $resul['headers'][$key] = $value;
                }

            if ($resul['headers']['Content-Type'] === 'application/xml') {
                $result="not found" ;
            }else{
                $result = substr($res, $header_size);
            }


            if (curl_errno($ch)) {
                curl_close($ch);
                //return false;
            }else{
                curl_close($ch);
                // return true;
            }
            return $result;
        }

	}
}