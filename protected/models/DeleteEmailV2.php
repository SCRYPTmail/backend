<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class DeleteEmailV2 extends CFormModel
{

	public $messageId,$modKey;


	public function rules()
	{
		return array(
			// deleteEmailUnreg
			array('messageId', 'match', 'pattern' => "/^[a-zA-Z0-9\d]+$/i", 'allowEmpty' => false, 'on' => 'deleteEmailUnreg','message'=>'fld2upd'),
			array('messageId','length', 'max'=>128,'allowEmpty' => false,'on'=>'deleteEmailUnreg','message'=>'fld2upd'),

			array('modKey', 'match', 'pattern' => "/^[a-z0-9\d]{32,64}$/i", 'allowEmpty' => false, 'on' => 'deleteEmailUnreg','message'=>'fld2upd'),

			//	array('mailHash', 'numerical','integerOnly'=>true,'allowEmpty'=>true),
		);
	}




	public function deleteEmailUnreg()
	{

		$result['response']='fail';


			if(strlen($this->messageId)===128){
				$emailToDeleteId=array('oldId'=>$this->messageId,'modKey'=>hash('sha512',$this->modKey));

				if(Yii::app()->mongo->removeAll('mailQueue',$emailToDeleteId)['n']==1){
					$result['response']='success';
				}

			}else if(strlen($this->messageId)===24){
				$emailToDeleteId=array('_id'=>new MongoId($this->messageId),'modKey'=>hash('sha512',$this->modKey));

				if(Yii::app()->mongo->removeAll('mailQv2',$emailToDeleteId)['n']==1){
					$result['response']='success';
				}

			}

		echo json_encode($result);

	}
}