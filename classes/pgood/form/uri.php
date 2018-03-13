<?php
namespace pgood\form;

class uri{
	protected $arURI;
	function __construct($strURI){
		$this->init($strURI);
	}
	private function init($strURI){
		if(preg_match('/^([^:]+):\/\/\/(.*)$/',$strURI,$m) && $m[1] != 'file')
			$strURI = $m[1].'://pgood/'.$m[2];
		$this->arURI = parse_url($strURI);
		if(isset($this->arURI['host']) && $this->arURI['host'] == 'pgood')
			unset($this->arURI['host']);
	}
	private function prop($name,$value = false){
		if($value === false){
			if(isset($this->arURI[$name]))
				return $this->arURI[$name];
		}else $this->arURI[$name] = $value;
	}
	function scheme($v = false){
		return $this->prop('scheme',$v);
	}
	function host($v = false){
		return $this->prop('host',$v);
	}
	function path($v = false){
		return $this->prop('path',$v);
	}
	function query($v = false){
		return $this->prop('query',$v);
	}
	function hash($v = false){
		return $this->prop('fragment',$v);
	}
	function fileExt(){
		if($this->path())
			return pathinfo($this->path(),PATHINFO_EXTENSION);
	}
	function filePath(){
		if($this->path())
			return substr($this->path(),1);
	}
	function replaceHolders($arValues){
		$strURI = $this.'';
		foreach($arValues as $holder => $value)
			$strURI = str_replace('%'.$holder.'%',$value,$strURI);
		$this->init($strURI);
	}
	function __toString(){
		extract($this->arURI);
		return (isset($scheme) ? $scheme.'://' : null)
			.(isset($host) ? $host : null)
			.(isset($port) ? $port : null)
			.(isset($user) ? $user : null)
			.(isset($pass) ? $pass : null)
			.(isset($path) ? $path : null)
			.(isset($query) ? '?'.$query : null)
			.(isset($fragment) ? '#'.$fragment : null);
	}
}