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
 * @package XML lib
 * @author Pavel Khoroshkov aka pgood
 * @link http://pgood.ru/
 */
namespace pgood\xml;

class template extends cached{
	function __construct($path){
		if(is_file($path))
			parent::__construct($path);
		else throw new \Exception('Wrong value <pre>'.print_r($path,1).'</pre>');
		$this->registerNameSpace('xsl','http://www.w3.org/1999/XSL/Transform');
	}
	function xslInclude($path){
		if($this->de() && !$this->evaluate('count(/xsl:stylesheet/xsl:include[@href="'.htmlspecialchars($path).'"])')){
			$include = $this->de()->insertBefore(
				$this->dd()->createElementNS('http://www.w3.org/1999/XSL/Transform','xsl:include')
				,$this->de()->firstChild);
			$include->href = $path;
		}
	}
	function xslVariable($name,$value){
		if(!$this->evaluate('count(/xsl:stylesheet/xsl:variable[@name="'.$name.'"])')){
			$e = new element($this->de()->insertBefore(
				$this->dd()->createElementNS('http://www.w3.org/1999/XSL/Transform','xsl:variable')
				,$this->de()->firstChild));
			$e->name = $name;
			$e->text($value);
		}
	}
	function xslImportTemplates(template $xsl){
		$ns = $xsl->query('//xsl:template | /*/xsl:variable');
		foreach($ns as $e)
			$this->de()->append($e);
	}
	function transform($xml){
		if($this->de()){
			$xml = new \pgood\xml\xml($xml);
			$proc = new \XSLTProcessor;
			$proc->importStyleSheet($this->dd());
			return $proc->transformToXML($xml->dd());
		}
		throw new \Exception('Template XML error');
	}
}