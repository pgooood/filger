<?php
namespace pgood\form;

class xmlScheme extends scheme{

protected $values = array();
protected $xmlCache = array();

protected function getXML($path){
	if(!isset($this->xmlCache[$path]))
		$this->xmlCache[$path] = new \pgood\xml\xml(substr($path,1));
	return $this->xmlCache[$path];
}
protected function node(uri $uri){
	if($uri->path()
		&& $uri->query()
		&& !$uri->hash()
	) return xmlScheme::getXML($uri->path())->query($uri->query())->item(0);
}
function value(uri $uri){
	if($e = xmlScheme::node($uri)) switch(true){
		case $e instanceof \DOMAttr:
			return $e->value;
		case $e instanceof \pgood\xml\element:
			return $e->text();
	}
}
function add(uri $uri,$value){
	if($uri->path() && $uri->query()){
		$this->values[$uri->path()][$uri->query()] = array('value'=>$value, 'hash'=> $uri->hash());
		return true;
	}
}
/**
* Создает недостающие элементы в xpath запросе
* @param type $xpath
* @param type $xml
*/
static function checkPath($xpath,$xml){
   $arElementsToCreate = array();
   $arXPath = explode('/',$xpath);
   $e = null;
   while($xp = implode('/',$arXPath)){
	   if($e = $xml->query($xp)->item(0)) break;
	   else array_unshift($arElementsToCreate,array_pop($arXPath));
   }
   if(!$e) $e = $xml;
   foreach($arElementsToCreate as $str){
	   if(preg_match('/^([\w\-]+)(?:\[((?:@[\w\-]+|@[\w\-]+=["\'][\w\-]+["\']|\s+|and|or|[0-9])+)\]){0,1}$/',$str,$res)){
		   //создаем элемент
		   $e = $e->append($xml->create($res[1]));
		   if(isset($res[2])){//если есть условие
			   //все атрибуты в условии запроса, у которых заданы значения, будут созданы, например tagname[@attr1="val1" and @attr2="val2"]
			   $tmp = explode(' ',$res[2]);
			   foreach($tmp as $str)
				   if(preg_match('/^@([\w\-]+)=["\']([\w\-]+)["\']$/',trim($str),$res)){
					   $e->{$res[1]} = $res[2];
				   }
		   }
	   }else break;
   }
   return $e;
}
function save(){
	foreach($this->values as $fpath => $values){
		$xml = xmlScheme::getXML($fpath);
		foreach($values as $xpath => $data){
			//создаем недостающие элементы из xpath запроса
			$elem = xmlScheme::checkPath($xpath,$xml);
			
			//Устанавливаем значения. Сначала получим целевой элемент
			if(($xpath=='/' && ($e = $xml->dd()))
				|| ($e = $xml->query($xpath)->item(0))
				|| (preg_match('/(.*)\/@([\w\-]+)$/',$xpath,$matches) //если конечная цель атрибут
					&& ($tmp = $xml->query($matches[1])->item(0))
					&& ($e = $tmp->setAttribute($matches[2],null)) //создаем его
				)
			){
				switch(true){
					case $e instanceof \DOMAttr:
						if($data['value']){
							if(!is_array($data['value'])) $e->value = $data['value'];
						}elseif($e->ownerElement->hasAttribute($e->name))
							$e->ownerElement->removeAttribute($e->name);
						break;
					case $e instanceof \DOMDocument:
					case $e instanceof \pgood\xml\element:
						if($data['hash']){
							if(preg_match('/^([\w\-]+)(?:\[((?:@[\w\-]+|@[\w\-]+=["\'][\w\-]+["\']|\s+|and)+)\]){0,1}$/',$data['hash'] = trim($data['hash']),$res)){
								$elem = $e->append($xml->create($res[1],null,is_array($data['value']) ? null : $data['value']));
								if(isset($res[2])){
									$tmp = explode('and',$res[2]);
									foreach($tmp as $attr) if(preg_match('/^@([\w\-]+)=["\']([\w\-]+)["\']$/',trim($attr),$res))
										$elem->{$res[1]} = $res[2];
								}
								if(is_array($data['value'])){
									foreach($data['value'] as $name => $value){
										if(strlen($value)) $elem->{$name} = $value;
										elseif($elem->hasAttribute($name))
											$elem->removeAttribute($name);
									}
								}
							}
						}else{
							if(is_array($data['value'])){
								foreach($data['value'] as $name => $value){
									if(strlen($value)) $elem->{$name} = $value;
									elseif($elem->hasAttribute($name))
										$elem->removeAttribute($name);
								}
							}else $elem->text($data['value']);
						}
						break;
				}
			}
		}
		$xml->save();
	}
}
function clear(){
	$this->values = array();
}
}