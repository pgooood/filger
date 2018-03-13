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
 * nodeList is a DOMNodeList wraper class
 * @package XML lib
 * @author Pavel Khoroshkov aka pgood
 * @link http://pgood.space/
 */
namespace pgood\xml;

class nodeList implements \Iterator{
	public $nl;
	private $i;
	function __construct(\DOMNodeList $nl){
		$this->nl = $nl;
		$this->i = 0;
	}
	function __get($name){
		if($name = 'length') return $this->nl->length;
	}
	function item($i){
		$v = $this->nl->item($i);
		switch(true){
			case $v instanceof \DOMElement:
				return new element($v);
			default:
				return $v;
		}
	}
	function rewind(){
		$this->i = 0;
	}
	function current(){
		return $this->item($this->i);
	}
	function key(){
		return $this->i;
	}
	function next(){
		$this->i++;
	}
	function valid(){
		return $this->i < $this->nl->length;
	}
}