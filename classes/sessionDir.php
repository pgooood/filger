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
 * sessionDir
 * @package Filger
 * @author Pavel Khoroshkov aka pgood
 * @link http://pgood.ru/
 */
class sessionDir{
	private $root;
	function __construct($root){
		$this->root = $root;
		session_start();
		if(!isset($_SESSION[SESSION_PEFIX.'path']))
			$_SESSION[SESSION_PEFIX.'path'] = array();
	}
	function path($rel = false){
		return ($rel ? $this->root : '/').(count($_SESSION[SESSION_PEFIX.'path']) ? implode('/',$_SESSION[SESSION_PEFIX.'path']).'/' : null);
	}
	function set($path){
		if(strlen($path)){
			if($path === '.');
			elseif($path === '..')
				array_pop($_SESSION[SESSION_PEFIX.'path']);
			elseif(isValidFileName($path) && is_dir($this->path(true).$path))
				array_push($_SESSION[SESSION_PEFIX.'path'],$path);
		}
	}
	function isRoot(){
		return !count($_SESSION[SESSION_PEFIX.'path']);
	}
	function orderBy($col,$dir){
		$_SESSION[SESSION_PEFIX.'order_col'] = $col;
		$_SESSION[SESSION_PEFIX.'order_dir'] = $dir;
	}
	function getOrderCol(){
		if(!isset($_SESSION[SESSION_PEFIX.'order_col']))
			$_SESSION[SESSION_PEFIX.'order_col'] = 'name';
		return $_SESSION[SESSION_PEFIX.'order_col'];
	}
	function getOrderDir(){
		if(!isset($_SESSION[SESSION_PEFIX.'order_dir']))
			$_SESSION[SESSION_PEFIX.'order_dir'] = 'asc';
		return $_SESSION[SESSION_PEFIX.'order_dir'];
	}
}