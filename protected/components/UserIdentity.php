<?php
/**
 * User: Sergei Krutov
 * https://scryptmail.com
 * Date: 11/29/14
 * Time: 3:28 PM
 */

class UserIdentity extends CUserIdentity
{
	private $_id;
	public $version;
	public $userData;

	public function authenticate()
	{
		$username = strtolower($this->username);

		//$user = UserV2->findUser($username);
		$classObj = new UserV2();
 		$user =$classObj->findUser($username);


		if ($user === null)
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		else if ($user['password'] !== crypt($this->password, $user['password']))
			$this->errorCode = self::ERROR_PASSWORD_INVALID;
		else {
			if($user['version']==1){
				$this->_id = $user['id'];
				$this->setState('version', $user['version']);

			}else if($user['version']==2){
				$this->_id = $user['_id'];
				$this->setState('version', $user['version']);
			}
			//$this->version = $user['version'];
			//$this->setVersion($user['version']);

			//print_r($this->_version);

			$this->username = $username;
			$this->errorCode = self::ERROR_NONE;
		}
		return $this->errorCode == self::ERROR_NONE;
	}

	public function getId()
	{
		return $this->_id;
	}


}