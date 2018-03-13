<?php
class in{
	protected $__request;
	function __construct(){
		$this->__request = $_REQUEST;
	}
	function session($name,$v = UNDEFINED){
		if(!session_id()) session_start();
		if($v === UNDEFINED)
			return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
		elseif($v === null){
			if(isset($_SESSION[$name]))
				unset($_SESSION[$name]);
		}else
			$_SESSION[$name] = $v;
	}
	function server($v){
		return filter_input(INPUT_SERVER,$v);  
	}
	function cookie($name,$value = UNDEFINED,$expire = 0,$path = null,$domain = null,$secure = false,$httponly = false){
		if($value === UNDEFINED)
			return filter_input(INPUT_COOKIE,$name);
		if($path === null)
			$path = $this->basePath();
		if($domain === null)
			$domain = $this->server('HTTP_HOST');
		setcookie($name,$value,$expire,$this->basePath(),$domain,$secure,$httponly);
		if($expire < time() && isset($_COOKIE[$name]))
			unset($_COOKIE[$name]);
	}
	function post(){
		return $_POST;
	}
	function get(){
		return $_GET;
	}
	function __set($name,$value){
		$this->__request[$name] = $value;
	}
	function __get($name){
		if(isset($this->__request[$name]))
			return $this->__request[$name];
	}
	function __isset($name){
		return isset($this->__request[$name]);
	}
	function __unset($name){
		unset($this->__request[$name]);
	}
}