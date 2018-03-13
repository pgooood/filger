<?php
/*
 * Copyright 2016 Pavel Khoroshkov
 */
/**
 * xmlResponse
 * @package Filger
 * @author Pavel Khoroshkov aka pgood
 * @link http://pgood.space/
 */
class xmlResponse extends \pgood\xml\xml{
	function __construct(){
		parent::__construct('message');
	}
	function setType($v){
		$this->de()->type = $v;
	}
	function success($message){
		$this->send($message,'success');
	}
	function error($message){
		$this->send($message,'error');
	}
	function send($message,$type = false){
		if($type !== false)
			$this->setType($type);
		$this->de()->text($message);
		header('Content-type: text/xml; charset=utf-8');
		echo $this;
		die;
	}
}