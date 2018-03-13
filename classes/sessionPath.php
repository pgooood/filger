<?php
/*
 * Copyright 2016 Pavel Khoroshkov
 */
/**
 * sessionPath
 * @package Filger
 * @author Pavel Khoroshkov aka pgood
 * @link http://pgood.space/
 */
class sessionPath{
	private $name;
	function __construct($name){
		if(!session_id())
			session_start();
		$this->name = $name;
		if(!isset($_SESSION[$this->name]) || !is_array($_SESSION[$this->name]) || !is_dir($this->relate(UPLOAD_ROOT_PATH)))
			$_SESSION[$this->name] = array();
	}
	function __toString(){
		return fenc(empty($_SESSION[$this->name]) ? '' : implode('/',array_filter($_SESSION[$this->name])).'/');
	}
	function change($dir){
		if(strlen($dir)){
			if($dir === '.');
			elseif($dir === '..')
				array_pop($_SESSION[$this->name]);
			elseif(isValidFileName($dir) && is_dir($this->relate(UPLOAD_ROOT_PATH).fenc($dir)))
				array_push($_SESSION[$this->name],$dir);
		}
	}
	function getArray(){
		return $_SESSION[$this->name];
	}
	function isRoot(){
		return !count($_SESSION[$this->name]);
	}
	function relate($path){
		if(strlen($path))
			return fenc($path.(substr($path,-1) == '/' ? null : '/')).$this;
	}
}