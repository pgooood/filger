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
 * element - is a DOMElement wraper class
 * @package XML lib
 * @author Pavel Khoroshkov aka pgood
 * @link http://pgood.space/
 */
namespace pgood\xml;

class element{
	private $__e;
	
	function __construct($v){
		if($v instanceof \DOMElement) $this->__e = $v;
		elseif($v instanceof element) $this->__e = $v->e();
		else throw new \Exception('Wrong value <pre>'.print_r($v,1).'</pre>');
	}
	function e(){
		return $this->__e;
	}
	function name(){
		return strtolower($this->e()->tagName);
	}
	function xml(){
		return new xml($this->e());
	}
	function query($v){
		return $this->xml()->query($v,$this->e());
	}
	function evaluate($v){
		return $this->xml()->evaluate($v,$this->e());
	}
	function getAttribute($name){
		return $this->e()->getAttribute($name);
	}
	function setAttribute($name,$value = null){
		if(is_array($name)){
			foreach($name as $n => $v)
				if($n) $this->setAttribute($n,$v);
		}else
			return $this->e()->setAttribute($name,$value);
	}
	function hasAttribute($name){
		return $this->e()->hasAttribute($name);
	}
	function removeAttribute($name){
		return $this->e()->removeAttribute($name);
	}
	function text($v = false){
		$e = $this->e();
		if($v !== false){
			foreach($e->childNodes as $n) if($n instanceof \DOMText) $e->removeChild($n);
			$e->appendChild($this->xml()->createTextNode($v));
		}
		$v = '';
		if($e->hasChildNodes())
			foreach($e->childNodes as $n) if($n instanceof \DOMText) $v.= $n->data;
		return $v;
	}
	function append($v){
		if(is_string($v))
			$v = $this->xml()->create($v);
		$eNative = $this->isNative($v);
		if((
				($eNative && ($n = $eNative->e()))
				|| ($n = $this->importNode($v))
			)
			&& ($n = $this->e()->appendChild($n))
		){
			if($eNative && $v instanceof element)
				return $v;
			if($n instanceof \DOMElement)
				return new element($n);
			return $n;
		}
	}
	function insertBefore($v,$refnode = null){
		if(is_string($v))
			$v = $this->xml()->create($v);
		if($n = $this->importNode($v)){
			if($refnode instanceof element)
				$refnode = $refnode->e();
			if($refnode instanceof \DOMNode)
				$n = $this->e()->insertBefore($n,$refnode);
			else
				$n = $this->append($n);
			if($n instanceof \DOMElement)
				return new element($n);
			return $n;
		}
	}
	function insertAfter($v){
		if($n = $this->next())
			return $n->insertBefore($v);
		return $this->append($v);
	}
	function parent(){
		if(($e = $this->e()->parentNode) && $e instanceof \DOMElement)
			return new element($e);
	}
	function next(){
		if($e = $this->e()->nextSibling)
			return new element($e);
	}
	function prev(){
		if($e = $this->e()->previousSibling)
			return new element($e);
	}
	function remove(){
		$this->e()->parentNode->removeChild($this->e());
	}
		function removeChild($v){
		$n = null;
		if($v instanceof \DOMNode) $n = $v;
		elseif($v instanceof element) $n = $v->e();
		if($n) return $this->e()->removeChild($n);
	}
	function cloneNode($deep = false){
		if($e = $this->e()->cloneNode($deep))
			return new element($e);
	}
	/*
	* Возращает обект переданного элемента, если элемент принадлежит к тому же документу
	*/
	private function isNative($v){
		$e = null;
		if($v instanceof \DOMElement) $e = new element($v);
		elseif($v instanceof element) $e = $v;
		if($e && $e->e()->ownerDocument->isSameNode($this->e()->ownerDocument))
			return $e;
	}
	private function importNode($v){
		$xml = $this->xml();
		$n = null;
		if($v instanceof \DOMNode) $n = $v;
		elseif($v instanceof element) $n = $v->e();
		elseif($v instanceof xml && $v->de()) $n = $v->de()->e();
		elseif(is_string($v)){
			try{
				$n = $xml->create($v);
			}catch(\DOMException $e){}
		}
		if($n) return $xml->importNode($n);
	}
	function __set($name,$value){
		switch($name){
			case 'firstChild': return;
		}
		if($value===null || $value===false){
			if($this->hasAttribute($name))
				$this->removeAttribute($name);
		}else
			$this->setAttribute($name,$value===true ? $name : $value);
	}
	function __get($name){
		switch($name){
			case 'firstChild':
				return $this->e()->firstChild;
		}
		return $this->getAttribute($name);
	}
	function __isset($name){
		return $this->e()->hasAttribute($name);
	}
	function __unset($name){
		if($this->e()->hasAttribute($name))
			$this->e()->removeAttribute($name);
	}
}