<?php
/*
 * Copyright 2021 Pavel Khoroshkov
 */
function classAutoload($class){
	$class = str_replace('\\','/',$class);
	if(is_file($path = 'classes/'.$class.'.php'))
		include($path);
}

function vdump($v,$die = false){
	$s = '<pre>'.htmlspecialchars(print_r($v,true)).'</pre>';
	if($die)
		throw new Exception($s);
	echo $s;
}

function isWindows(){
	return isset($_SERVER['WINDIR']);
}

function fdec($filename){
	return $filename;
}

function fenc($filename){
	return $filename;
}

function checkPHP(){
	PHP_MAJOR_VERSION > 5 || (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION >= 3)
		|| die('PHP version of at least 5.3 is required for Filger to work properly');
	extension_loaded('xsl')
		|| die('Filger requires PHP with XSL support');
}

function cleanTemp($dir){
	$sd = scandir($dir);
	foreach($sd as $item){
		if(is_dir($path = $dir.'/'.$item))
			continue;
		if((fileatime($path) < time() - 86400)
			&& (is_writable($path) || (chmod($path,0777) && is_writable($path)))
		) unlink($path);
	}
}

function fileTransfer($path,$contentType,$fileName){
	header('Content-Description: File Transfer');
	header('Content-Type: '.$contentType);
	header('Content-Disposition: attachment; filename*=UTF-8\'\''.rawurlencode($fileName));
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: '.filesize($path));
	readfile($path);
	die();
}

function initGettext($locale,$localePath,$domain){
	putenv('LANG='.$locale);
	putenv('LANGUAGE='.$locale);
	putenv('LC_MESSAGES='.$locale);
	setlocale(defined('LC_MESSAGES') ? LC_MESSAGES : LC_ALL,$locale.'.UTF-8');
	bindtextdomain($domain,$localePath);
	bind_textdomain_codeset($domain,'UTF-8');
	textdomain($domain);
}

function param($name,$filter = FILTER_DEFAULT){
	if(isset($_REQUEST[$name])){
		if(is_array($_REQUEST[$name]))
			return filter_var_array($_REQUEST[$name],$filter);
		return filter_var($_REQUEST[$name],$filter);
	}
}

function sess($name,$v = UNDEFINED){
	$name = SESSION_PREFIX.$name;
	if($v === UNDEFINED){
		if(isset($_SESSION[$name]))
			return $_SESSION[$name];
	}else
		return $_SESSION[$name] = $v;
}

function removeDir($dir){
	if(!file_exists($dir)) return true;
	if(!is_dir($dir) || is_link($dir))
		if(is_writable($dir) || (chmod($dir,0777) && is_writable($dir)))
			return unlink($dir);
		else return false;
	$sd = scandir($dir);
	foreach($sd as $item){
		if($item == '.' || $item == '..') continue;
		$path = $dir.'/'.$item;
		if(!removeDir($path) && (!chmod($path,0777) || !removeDir($path)))
			return false;
	}
	return rmdir($dir);
}

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

function isValidFileName($v){
	return !preg_match('/[<>:"\/\\\\|\?\*]/',$v) && mb_strlen($v) < 256;
}

/**
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

function installFileUpload($fileUploadDownloadUrl,$fileUploadDir){
	$path = null;
	if((!is_dir($fileUploadDir) || removeDir($fileUploadDir))
		&& mkdir($fileUploadDir)
		&& ($h = fopen($fileUploadDownloadUrl,'r'))
		&& file_put_contents($zipPath = $fileUploadDir.'jQuery-File-Upload.zip',$h) !== false
		&& ($zip = new ZipArchive())
		&& $zip->open($zipPath) === true
		&& $zip->extractTo($fileUploadDir)
	){
		$zip->close();
		if(is_dir($path = $fileUploadDir.'jQuery-File-Upload-master/')){
			//remove dangerous files
			foreach(scandir($dir = $path.'server/') as $item){
				if($item == '.' || $item == '..') continue;
				if($item == 'php'){
					if(is_file($pathUploader = $dir.'/'.$item.'/index.php'))
						rename($pathUploader,$pathUploader.'.bak');
				}else
					removeDir($dir.'/'.$item);
			}
		}
		unlink($zipPath);
	}
	return $path;
}

function defineConfigConst(){
	$arDefines = array(
		'UPLOAD_ROOT_PATH'		=> array('xpath' => 'upload/path','required' => true)
		,'UPLOAD_ROOT_URL'		=> array('xpath' => 'upload/url','required' => true)
		,'FILE_UPLOADER_PATH'	=> array('xpath' => 'upload/uploader','required' => true)
		,'LOCALE'				=> array('xpath' => 'locale/lang','required' => true)
		,'WIN_CP'				=> array('xpath' => 'locale/win_cp','required' => isWindows())
		,'DOS_CP'				=> array('xpath' => 'locale/dos_cp','required' => isWindows())
		,'DATE_FORMAT'			=> array('xpath' => 'locale/date','required' => true)
		,'DEC_POINT'			=> array('xpath' => 'locale/dec_point','required' => true)
		,'THOUSANDS_SEP'		=> array('xpath' => 'locale/thousands_sep','required' => true)
		,'AUTH_PATH'			=> array('xpath' => 'authenticator','required' => false)
	);
	$conf = new \pgood\xml\cached(CONFIG_PATH);
	foreach($arDefines as $name => $arData)
		if(!defined($name)){
			if(strlen($v = $conf->evaluate('string(/config/'.$arData['xpath'].')')))
				define($name,$v);
			elseif($arData['required'])
				throw new Exception('There is no config value '.$arData['xpath']);
		}
}
function getFileUploadOptions(){
	$arOptions = array(
		'max_number_of_files'	=> 999
		,'accept_file_types'	=> '/.+\.(jpe?g|png|gif|pdf|docx?|xlsx?|txt|zip)$/i'
		,'image_versions'		=> array()
		,'max_file_size'		=> 2097152 //2MB
	);
	$conf = new \pgood\xml\cached(CONFIG_PATH);
	foreach($arOptions as $name => $defVal)
		if(strlen($v = $conf->evaluate('string(/config/file_upload/'.$name.')')))
			$arOptions[$name] = $v;
	return $arOptions;
}