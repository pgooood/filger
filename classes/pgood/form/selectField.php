<?php
namespace pgood\form;

class selectField extends \pgood\form\field{
	function value($v = UNDEFINED){
		if($v === UNDEFINED)
			return $this->value;
		$this->value = $v;
		if($e = $this->query('option[@value="'.$v.'"]')->item(0))
			$e->selected = true;
	}
	function addOption($text,$value){
		$o = new selectFieldOption($this->append('option'));
		$o->value($value);
		$o->text($text);
		return $o;
	}
	function options(){
		$ar = array();
		$ns = $this->query('option');
		foreach($ns as $e)
			$ar[] = new selectFieldOption($e);
		return $ar;
	}
}

class selectFieldOption extends \pgood\xml\element{
	function value($v = false){
		if($v === false)
			return $this->value;
		$this->value = $v;
	}
}