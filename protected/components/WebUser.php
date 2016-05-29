<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class WebUser extends CWebUser
{
	private $_model = null;
	public $loginUrl = null;


	function getRole($type = null)
	{
		if (isset(Yii::app()->user->id)) {
			//$groups = User::getGroups($this->id);

			$classObj = new UserV2();
			if($this->version==1){
				if ($role = $classObj->getRole($this->id)) {
					$role['role'] = 1;
					$role['plan'] = $role['plan'];
					$role['isDue'] = $role['isDue'];
				} else {
					$role['role'] = 0;
					$role['plan'] = array();
					$role['isDue'] = false;
				}
			}else if($this->version==2){
				if ($role = $classObj->getRole($this->id)) {
					$role['role'] = 1;
					$role['plan'] = $role['plan'];
					$role['isDue'] = $role['isDue'];
				} else {
					$role['role'] = 0;
					$role['plan'] = array();
					$role['isDue'] = false;
				}
			}





		} else {
			$role['role'] = 0;
			$role['plan'] = array();
			$role['isDue'] = false;
		}

		return $role;

	}


	public function getUserData()
	{
		return $this->getState('userData');
	}

	public function getVersion()
	{
		return $this->version;
	}

}

?>