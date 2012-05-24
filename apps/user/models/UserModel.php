<?php

namespace apps\user;

use apps\ApplicationException;

class UserModel extends \base\User {
	
	protected $controller = NULL;
	
	protected $post = NULL;
	
	public function __construct(&$controller){
		$this->controller = &$controller;
		$this->setUser($this->controller->getSandbox()->getService('user')->getUser());
		$this->init();
	}
	
	public function init(){
		$email = $this->postVariable('email');
		$login = $this->postVariable('login');
		$password = $this->postVariable('password');
		$this->input['email'] = $this->isEmail($email) ? $email : NULL;
		$this->input['login'] = is_null($login) ? $email : $login;
		$this->input['password'] = $this->isGoodPassword($password) ? md5($password) : NULL;
		$this->input['passwordcurrent'] = md5($this->postVariable('passwordcurrent'));
		$this->input['passwordconfirm'] = md5($this->postVariable('passwordconfirm'));
		$this->input['creationTime'] = time();
	}
	
	protected function postVariable($key){
		if(array_key_exists($key, $_POST)){
			return trim($_POST[$key]);
		}else{
			return NULL;
		}
	}
	
	public function validateSignUp(){
		if(is_null($this->input['email'])) {
			throw new \apps\ApplicationException($this->controller->translate("invalid.email"));
		}
		if(is_null($this->input['login'])){
			throw new \apps\ApplicationException($this->controller->translate("invalid.login"));
		}
		if(is_null($this->input['password'])){
			throw new \apps\ApplicationException($this->controller->translate("invalid.password"));
		}
		if($this->input['password'] != $this->input['passwordconfirm']){
			throw new \apps\ApplicationException($this->controller->translate("mismatched.passwords"));
		}
	}
	
	public function signUp(){
		try {
			$storage = $this->controller->getSandbox()->getService('storage');
			unset($this->input['passwordconfirm']);
			$storage->insert(array('table' => 'user', 'content' => $this->input));
			$this->setID($storage->getInsertID());
			$this->setUser($this->input);
			$this->isGuest(false);
			$this->controller->getSandbox()->getService('session')->write('user', $this->getUser());
		}catch(\base\BaseException $e){
			throw new \apps\ApplicationException($e->getMessage());
		}
	}
	
	public function validateSignIn(){
		if(is_null($this->input['login'])){
			throw new \apps\ApplicationException($this->controller->translate("invalid.login"));
		}
		if(is_null($this->input['password'])){
			throw new \apps\ApplicationException($this->controller->translate("incorrect.password"));
		}
		$storage = $this->controller->getSandbox()->getService('storage');
		$login = $storage->sanitize($this->input['login']);
		$sql = sprintf("SELECT * FROM `user` WHERE `login` = '%s'", $login);
		$rows = $storage->query($sql);
		if(!is_null($rows)){
			$result = $rows[0];
			if($this->input['password'] === $result['password']){
				$result['roles'] = $this->fetchRoles($result['ID']);
				return $result;
			}else{
				throw new \apps\ApplicationException($this->controller->translate("incorrect.password"));
			}
		}else{
			throw new \apps\ApplicationException($this->controller->translate("incorrect.login"));
		}
	}
	
	public function signIn($result){
		$storage = $this->controller->getSandbox()->getService('storage');
		$this->setUser($result);
		$session = $this->controller->getSandbox()->getService('session');
		$session->write('user', $this->getUser());
	}
	
	public function ownGuest($result){
		if(!$this->isGuest()) return;
		$update['table'] = 'guest';
		$update['content'] = array('user' => $result['ID']);
		$update['constraints'] = array('ID' => $this->getID());
		$this->controller->getSandbox()->getService('storage')->update($update);
	}
	
	protected function fetchRoles($ID){
		$sql = sprintf("SELECT `title` FROM `user_role` LEFT JOIN `role` ON (`user_role`.`role` = `role`.`ID`) WHERE `user` = %d", $ID);
		try {
			$result = $this->controller->getSandbox()->getService('storage')->query($sql);
			if(!is_null($result)){
				foreach($result as $row){
					$roles[] = $row['title'];
				}
				return $roles;
			} else {
				return NULL;
			}
		}catch(\base\BaseException $e){
			throw new \apps\ApplicationException($e->getMessage());
		}
	}
	
	public function signOut(){
		$session = $this->controller->getSandbox()->getService('session');
		$session->purge();
	}
	
	public function validateChangePassword(){
		if($this->isGuest()){
			throw new \apps\ApplicationException($this->controller->translate("incorrect.login"));
		}
		if(is_null($this->input['password'])){
			throw new \apps\ApplicationException($this->controller->translate("invalid.password"));
		}
		if($this->input['password'] != $this->input['passwordconfirm']){
			throw new \apps\ApplicationException($this->controller->translate("mismatched.passwords"));
		}
		if($this->input['passwordcurrent'] != $this->getPassword()){
			throw new \apps\ApplicationException($this->controller->translate("incorrect.password"));
		}
	}
	
	public function changePassword(){
		$update['table'] = 'user';
		$update['content'] = array('password' => $this->input['password']);
		$update['constraints'] = array('ID' => $this->getID());
		try {
			$this->controller->getSandbox()->getService("storage")->update($update);
		}catch(\base\BaseException $e){
			throw new \apps\ApplicationException($e->getMessage());
		}
	}
	
	protected function resetPassword(){
		
	}
	
	protected function isEmail($input){
		return filter_var(filter_var($input, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
	}
	
	protected function isGoodPassword($input){
		$length = strlen($input);
		if($length < 6 || $length > 32) {
			return false;
		} else {
			return true;
		}
	}
	
	protected function initFormModel(){
		$base = $this->controller->getSandbox()->getMeta('base');
		require_once("$base/apps/studio/models/FormModel.php");
		return new \apps\studio\FormModel($this->controller);
	}
	
	public function getForm($template, $action=NULL) {
		$form = $this->initFormModel();
		$form->setSource($template);
		if(!is_null($action)) 
			$form->setAction($action);
		$body = $form->asHTML();
		$title = $this->controller->getSandbox()->getService('translation')->translate($template);
		return array("title" => $title, "body" => $body);
	}
	
}
?>