<?php
namespace pgood\form;

class form extends \pgood\xml\element{
	protected $errors;

	function __construct($v = false){
		if($v === false){
			$xml = new \pgood\xml\xml('form');
			parent::__construct($xml->de());
		}elseif(is_object($v) && $v instanceof \pgood\xml\element){
			$xml = new \pgood\xml\xml();
			parent::__construct($xml->append($v));
		}
		if($message = $this->success())
			$xml->de()->append($xml->create('success',null,$message));
		if($message = $this->error())
			$xml->de()->append($xml->create('error',null,$message));
	}
	function method(){
		return $this->method;
	}
	function fields($cond = false){
		$ns = $this->query('.//field'.($cond ? '['.$cond.']' : null));
		$ar = array();
		foreach($ns as $e)
			if($ff = field::wrap($e))
				$ar[] = $ff;
		if(count($ar))
				return $ar;
	}
	function field($name){
		if($ar = $this->fields('@name="'.$name.'"'))
			return $ar[0];
	}
	function append($v){
		if(($res = parent::append($v))
			&& $res instanceof \pgood\xml\element
			&& ($ff = field::wrap($res))
		) $res = $ff;
		return $res;
	}
	/**
	 * Save values
	 * @param array $arValues - array('field name' => 'value')
	 * @return true or null
	 */
	function save($arValues = UNDEFINED){
		$this->fill($arValues);
		if($this->validate()
			&& ($arFields = $this->fields())
		){
			$arScheme = array();
			foreach($arFields as $f)
				if($scheme = $f->scheme()){
					if(!isset($arScheme[$scheme]))
						$arScheme[$scheme] = $f->scheme(true);
					$arScheme[$scheme]->add($f->uri(),$f->value());
				}
			foreach($arScheme as $scheme)
				$scheme->save();
			return true;
		}
	}
	function load(){
		$fields = $this->fields();
		foreach($fields as $f)
			$f->load();
	}
	function replaceUriHolders($arValues){
		if($fields = $this->fields())
			foreach($fields as $f)
				$f->replaceUriHolders($arValues);
	}
	function toArray(){
		$ar = array();
		if($fields = $this->fields())
			foreach($fields as $f)
				$ar[$f->name()] = $f->value();
		return $ar;
	}
	function id($v = UNDEFINED){
		if($v === UNDEFINED)
			return $this->id;
		$this->id = $v;
	}
	function sent($field = 'action'){
		if(($ff = $this->field($field)) && ($v = $ff->inputValue()))
			return  $ff->value === $v;
	}
	function fill($arValues = UNDEFINED){
		if($arFields = $this->fields()){
			if($arValues === UNDEFINED)
				foreach($arFields as $f)
					$f->value($f->inputValue());
			elseif(is_array($arValues))
				foreach($arFields as $f)
					$f->value(isset($arValues[$f->name()]) ? $arValues[$f->name()] : null);
			return true;
		}
	}
	function validate(){
		if($fields = $this->fields()){
			$this->errors = array();
			foreach($fields as $f)
				if(!$f->validate() || $f->error())
					$this->errors[$f->name()] = $f->error();
			return empty($this->errors);
		}
	}
	function errors(){
		return $this->errors;
	}
	function success($message = UNDEFINED){
		global $_in;
		if(!session_id())
			session_start();
		$name = $this->id().'_success_message';
		if($message === UNDEFINED){
			$v = $_in->session($name);
			$_in->session($name,null);
			return $v;
		}
		$_in->session($name,$message);
	}
	function error($message = UNDEFINED){
		global $_in;
		if(!session_id())
			session_start();
		$name = $this->id().'_error_message';
		if($message === UNDEFINED){
			$v = $_in->session($name);
			$_in->session($name,null);
			return $v;
		}
		$_in->session($name,$message);
	}
}