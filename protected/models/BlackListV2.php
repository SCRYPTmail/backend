<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class BlackListV2 extends CFormModel
{

	public $userToken;
	public $ruleId,$matchField,$text,$destination;



	public function rules()
	{
		return array(
			array('userToken', 'chkToken'),

			//array('vrfString', 'match', 'pattern' => "/^[a-z0-9\d]{64}$/i", 'allowEmpty' => false, 'on' => 'addPending','message'=>'chckVrf'),

			//array('domain', 'url', 'defaultScheme' => 'http', 'on' => 'addPending','message'=>'addPending')

            //saveNewEmailOld

            array('ruleId', 'match', 'pattern' => "/^[a-z0-9\d]{24}$/i", 'allowEmpty' => true, 'on' => 'saveRule','message'=>'fld2upd'),

            array('matchField', 'in', 'range' => array('domainM','domainNM','emailM','emailNM'), 'allowEmpty' => false, 'on' => 'saveRule','message'=>'fld2upd'),
            array('text','length', 'max'=>255,'allowEmpty' => false,'on'=>'saveRule','message'=>'fld2upd'),
            array('destination', 'numerical','integerOnly'=>true,'allowEmpty'=>false, 'on' => 'saveRule','message'=>'fld2upd'),

            //deleteRule
            array('ruleId', 'match', 'pattern' => "/^[a-z0-9\d]{24}$/i", 'allowEmpty' => true, 'on' => 'deleteRule
            ','message'=>'fld2upd'),

        );
	}


	public function chkToken(){
		$UserLoginTokenV2 = new UserLoginTokenV2();
		$UserLoginTokenV2->userToken=$this->userToken;

		if(!$UserLoginTokenV2->verifyUserLoginToken()){
			$this->addError('userToken', 'incToken');
		}
	}


    public function deleteAll($userId)
    {
        $criteria=array('userId'=>$userId);

        Yii::app()->mongo->removeAll('blackList',$criteria);
        $result['response']='success';
        $result['data']='saved';

        echo json_encode($result);
    }



    public function deleteRule($userId)
    {
        $criteria=array("_id" =>new MongoId($this->ruleId), 'userId'=>$userId);

        Yii::app()->mongo->removeAll('blackList',$criteria);
        $result['response']='success';
        $result['data']='saved';

        echo json_encode($result);
    }


    public function saveRule($userId)
    {
        $result['response']='fail';

            if($this->matchField==="emailM"){
                $mf=1;
            }else if($this->matchField==="emailMN"){
                $mf=2;
            } else if($this->matchField==="domainM"){
                $mf=3;
            }else if($this->matchField==="domainNM"){
                $mf=4;
            }

        $listObj=array(
            'mF'=>$mf,
            'txt'=>base64_encode($this->text),
            'dest'=>(int)$this->destination,
            'userId'=>$userId
        );

        if(isset($this->ruleId) && !empty($this->ruleId)){
            $criteria=array("_id" =>new MongoId($this->ruleId), 'userId'=>$userId);



        }else{
            $criteria=array("userId" => $userId,'mF'=>$mf,'txt'=>base64_encode($this->text));


        }

        if($user=Yii::app()->mongo->upsert('blackList',$listObj,$criteria)){
            $result['response']='success';
            $result['data']='saved';
        }

        echo json_encode($result);

    }


    public function getEntries($userId)
    {

        $criteria=array('userId'=>$userId);

        if($data64=Yii::app()->mongo->findAll('blackList',$criteria)){

        }else{
            $data64=array();
        }


        $result['response']='success';
        $result['data']=$data64;

        echo json_encode($result);
    }
}
