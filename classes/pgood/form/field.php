<?php
namespace pgood\form;

class field extends \pgood\xml\element{
	static function create($name,$label,$uri = null){
		$xml = new \pgood\xml\xml('field');
		$e = $xml->de();
		$e->type = preg_match('/([^\\\\]+)Field$/',get_called_class(),$m) ? $m[1] : 'text';
		$e->name = $name;
		$e->label = $label;
		if($uri)
			$e->uri = $uri.'';
		return new field($e);
	}
	static function wrap(\pgood\xml\element $e){
		if($e->name() == 'field'){
			if(class_exists($className = '\\pgood\\form\\'.strtolower($e->type).'Field',true))
				return new $className($e);
			else
				return new field($e);
		}
	}
	function name(){
		return $this->name;
	}
	function type(){
		return $this->type;
	}
	function label(){
		return $this->label;
	}
	function help($v = UNDEFINED){
		if($v === UNDEFINED)
			return $this->help;
		$this->help = $v;
	}
	function error($v = UNDEFINED){
		if($v === UNDEFINED){
			return $this->evaluate('string(error)');
		}else{
			$e = $this->query('error')->item(0);
			if($v && ($e || ($e = $this->append('error'))))
				$e->text($v);
			elseif($e)
				$e->remove();
		}
	}
	function uri($v = UNDEFINED){
		if($v === UNDEFINED){
			if($this->uri){
				$uri = new uri($this->uri);
				//ищем базовый урл
				$e = $this;
				while(!$uri->scheme() && ($e = $e->parent()))
					if($e->baseURI)
						$uri = new uri($e->baseURI.$this->uri);
				return $uri;
			}
		}else $this->uri = $v.'';
	}
	function scheme($object = false){
		if($uri = $this->uri()){
			if($uri->scheme()
				&& class_exists($className = '\\pgood\\form\\'.($uri->scheme()=='file' ? $uri->fileExt() : $uri->scheme()).'Scheme',true)
			)
				return $object ? new $className() : $className;
			else
				throw new \Exception('Scheme not found'.$uri);
		}
	}
	function value($v = UNDEFINED){
		if($v === UNDEFINED)
			return $this->text();
		else
			$this->text($v);
	}
	function load(){
		if($scheme = $this->scheme(true))
			$this->value($scheme->value($this->uri()));
	}
	function validate($v = UNDEFINED){
		if($v === UNDEFINED)
			$v = $this->value();
		if($isError = $this->required && !$v)
			$this->error(sprintf(__('%s required'),$this->label()));
		return !$isError;
	}
	function replaceUriHolders($arValues){
		if($uri = $this->uri()){
			$uri->replaceHolders($arValues);
			$this->uri($uri);
		}
	}
	function inputValue(){
		if('post' == strtolower($this->evaluate('string(ancestor::form/@method)')))
			return filter_input(INPUT_POST,$this->name());
		return filter_input(INPUT_GET,$this->name());
	}
}