<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class GetPublicKeysV2 extends CFormModel
{

	public $userToken;

	public $recepientMailHashes;

	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),
			array('recepientMailHashes', 'safe'),

			//publicKey


		);
	}

	public function chkToken(){

		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}

	public function ifWeOwn()
	{
		$result['response']="success";
		$hashes=json_decode($this->recepientMailHashes,true);
		$data=array();
		//$result['data']=
		//print_r(json_encode($this->recepientMailHashes,true));


		if(is_array($hashes) && count($hashes)>0){
			foreach($hashes as $i=>$row){
				$mngData[]=array('addressHash'=>$row);
			}
			$mngDataAgregate=array('$or'=>$mngData);

			if($dbKeys=Yii::app()->mongo->findAll('addresses',$mngDataAgregate,array('addressHash'=>1))){
				foreach($dbKeys as $row){
					$data[]=$row['addressHash'];
				}
			}
		}
		$result['data']=$data;
		echo json_encode($result);

	}


	public function getKeys()
	{
		$result['response']="success";
		$hashes=json_decode($this->recepientMailHashes,true);
		//$result['data']=
		//print_r(json_encode($this->recepientMailHashes,true));



		if(is_array($hashes)){
			foreach($hashes as $i=>$row){
				$mngData[]=array('addressHash'=>$row);
			}
			$mngDataAgregate=array('$or'=>$mngData);

			if($dbKeys=Yii::app()->mongo->findAll('addresses',$mngDataAgregate,array('addressHash'=>1,'mailKey'=>1))){
				foreach($dbKeys as $row){
					$data[$row['addressHash']]=$row['mailKey'];
				}
			}
		}
		$result['data']=$data;

		echo json_encode($result);

	}

}