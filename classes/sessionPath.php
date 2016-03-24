<?php
/*
Copyright 2016 Pavel Khoroshkov

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/
/**
 * sessionPath
 * @package Filger
 * @author Pavel Khoroshkov aka pgood
 * @link http://pgood.ru/
 */
class sessionPath{
	private $name;
	function __construct($name){
		if(!session_id())
			session_start();
		$this->name = $name;
		if(!isset($_SESSION[$this->name]))
			$_SESSION[$this->name] = array();
	}
	function __toString(){
		return implode('/',$_SESSION[$this->name]).'/';
	}
	function change($dir){
		if(strlen($dir)){
			if($dir === '.');
			elseif($dir === '..')
				array_pop($_SESSION[$this->name]);
			elseif(isValidFileName($dir) && is_dir($this->relate(UPLOAD_ROOT_PATH).$dir))
				array_push($_SESSION[$this->name],$dir);
		}
	}
	function isRoot(){
		return !count($_SESSION[$this->name]);
	}
	function relate($path){
		if(strlen($path))
			return $path.(substr($path,-1) == '/' ? null : '/').$this;
	}
}