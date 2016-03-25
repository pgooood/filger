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
spl_autoload_register(function($class){
	$class = str_replace('\\','/',$class);
	if(is_file($path = 'classes/'.$class.'.php'))
		include($path);
});

function vdump($v,$die = false){
	$s = '<pre>'.htmlspecialchars(print_r($v,true)).'</pre>';
	if($die)
		throw new Exception($s);
	echo $s;
}

/**
 * initGettext
 */
function initGettext($locale,$localePath,$domain){
	putenv('LANG='.$locale);
	putenv('LANGUAGE='.$locale);
	putenv('LC_MESSAGES='.$locale);
	setlocale(defined('LC_MESSAGES') ? LC_MESSAGES : LC_ALL,$locale);
	bindtextdomain($domain,$localePath);
	bind_textdomain_codeset($domain,'UTF-8');
	textdomain($domain);
}

/**
 * param
 */
function param($name,$filter = FILTER_DEFAULT){
	if(isset($_REQUEST[$name]))
		return filter_var($_REQUEST[$name],$filter);
}

/**
 * removeDir
 */
function removeDir($dir){
	if(!file_exists($dir)) return true;
	if(!is_dir($dir) || is_link($dir)) return unlink($dir);
	foreach(scandir($dir) as $item){
		if($item == '.' || $item == '..') continue;
		$path = $dir.'/'.$item;
		if(!removeDir($path)){
			chmod($path,0777);
			if(!removeDir($path)) return false;
		}
	}
	return rmdir($dir);
}

/**
 * formatFileSize
 */
function formatFileSize($bytes){
	$arUnits = array(_('B'),_('KB'),_('MB'),_('GB'),_('TB'),_('PB'),_('EB'));
	$v = $res = $bytes;
	$unitIndex = 0;
	while(($v = $v / 1024) > 1 && $unitIndex < count($arUnits)){
		$unitIndex++;
		$res = $v;
	}
	return trim(number_format($res,2,DEC_POINT,THOUSANDS_SEP),'0'.DEC_POINT).' '.$arUnits[$unitIndex];
}

/**
 * isValidFileName
 */
function isValidFileName($v){
	return !preg_match('/[<>:"\/\\\\|\?\*]/',$v) && mb_strlen($v) < 256;
}

/**
 * getTemplate
 * @return \pgood\xml\template object
 */
function getTemplate($name,$arTemplatesToImport = null){
	if($name
		&& isValidFileName($name = $name.'.xsl')
		&& is_file($path = XSL_PATH.$name)
	){
		$tpl = new \pgood\xml\template($path);
		if(is_array($arTemplatesToImport))
			foreach($arTemplatesToImport as $name)
				$tpl->xslImportTemplates(new \pgood\xml\template(XSL_PATH.$name.'.xsl'));
		$ns = $tpl->query('//xsl:text[@lang]');
		foreach($ns as $e)
			$e->text(_($e->text()));
		return $tpl;
	}
}