<?php
/*
 * Copyright 2018 Pavel Khoroshkov
 */
require_once 'include/config.php';
require_once 'include/lib.php';

if(!defined('DIRECTORY_SEPARATOR'))
	define('DIRECTORY_SEPARATOR',isWindows() ? '\\' : '/');
	
if(!session_id())
	session_start();

try{
checkPHP();
spl_autoload_register('classAutoload');
defineConfigConst();
initGettext(LOCALE,LOCALE_PATH,TEXT_DOMAIN);

if(defined('AUTH_PATH') && is_file(AUTH_PATH))
	require_once AUTH_PATH;

$response = new xmlResponse();
$out = new out();
$action = $out->installRequired() ? 'install' : param('action');
$spath = new sessionPath(SESSION_PREFIX.'path');
$currentPath = $spath->relate(UPLOAD_ROOT_PATH);
$currentUrl = $spath->relate(UPLOAD_ROOT_PATH);

switch($action){
	case 'sort':
		sess('order_col',param('col'));
		sess('order_dir',param('dir'));
		break;
	case 'upload':
		require_once($out->de()->fileUploaderPath.'server/php/UploadHandler.php');
		if(!is_array($arFileUploadOptions = getFileUploadOptions()))
			$arFileUploadOptions = array();
		$arFileUploadOptions['upload_dir'] = $currentPath;
		$arFileUploadOptions['upload_url'] = $currentUrl;
		new filgerUploadHandler($arFileUploadOptions);
		die;
	case 'mode':
		sess('mode',param('mode'));
		break;
	case 'remove':
		$ar = array();
		if(isset($_REQUEST['dir']) && is_array($_REQUEST['dir']))
			$ar = array_merge($ar,$_REQUEST['dir']);
		if(isset($_REQUEST['file']) && is_array($_REQUEST['file']))
			$ar = array_merge($ar,$_REQUEST['file']);
		foreach($ar as $name)
			if(!removeDir($currentPath.fenc($name)) )
				$response->error('fail');
			$response->success('ok');
		break;
	case 'rename_dir':
		if(isValidFileName($oldName = fenc(param('old')))
			&& ($newName = fenc(trim(param('new'))))
			&& is_dir($oldPath = $currentPath.$oldName)
		){
			if(isValidFileName($newName)){
				if(is_dir($newPath = $currentPath.$newName))
					$response->error(_('Such name already exists'));
				if(rename($oldPath,$newPath))
					$response->success(_('Directory renamed'));
				else
					$response->error(_('Renaming error!'));
			}else
				$response->error(_('Invalid directory name'));
		}else
			$response->error(_('Directory not found'));
		break;
	case 'rename_file':
		if(isValidFileName($oldName = fenc(param('old')))
			&& ($newName = fenc(trim(param('new'))))
			&& is_file($oldPath = $currentPath.$oldName)
		){
			if(isValidFileName($newName)){
				if(($arFileUploadOptions = getFileUploadOptions())
					&& !empty($arFileUploadOptions['accept_file_types'])
					&& !preg_match($arFileUploadOptions['accept_file_types'],$newName)
				){
					$response->error(_('Invalid file type!'));
				}
				if(is_file($newPath = $currentPath.$newName))
					$response->error(_('Such name already exists'));
				if(rename($oldPath,$newPath))
					$response->success(_('File renamed'));
				else
					$response->error(_('Renaming error!'));
			}else
				$response->error(_('Invalid file name'));
		}else
			$response->error(_('File not found'));
		break;
	case 'new_folder':
		if(isValidFileName($name = fenc(param('name')))){
			if(is_dir($currentPath.$name))
				$response->error(_('Error! Directory already exists.'));
			if(mkdir($currentPath.$name))
				$response->success(_('Directory created'));
			else
				$response->error(_('Error! Directory hasn\'t been created.'));
		}else
			$response->error(_('Invalid name'));
		break;
	case 'download':
		if(param('file') || $response->error(_('Nothing to download'))){
			$arItems = $file = fenc(param('file'));
			if(count($arItems) > 1){
				cleanTemp(TEMP_DIR);
				$arch = new archive($zipFile = TEMP_DIR.uniqid('download-').'.zip');
				if($arch->zip($currentPath,$arItems))
					fileTransfer($zipFile,'application/zip','filger_'.date('y-m-d_His').'.zip');
				else
					$out->alert($arch->getError());
			}elseif(count($arItems) === 1){
				if(is_file($file = $currentPath.$arItems[0]))
					fileTransfer($file,'application/octet-stream',fdec($arItems[0]));
				else
					$out->alert(_('File not found'));
			}else
				$out->alert($out->getMessage('download_nothing_selected'));
		}
		break;
	case 'install':
		require_once 'include/install.php';
		die;
	default:
		$arPath = array_filter(explode('/',param('path')));
		foreach($arPath as $dir)
			$spath->change($dir);
}

$dir = new xmlDir($spath,sess('order_col'),sess('order_dir'),sess('mode'),param('page'));
if(param('xml')){
	header("Content-type: text/xml; charset=utf-8");
	echo $dir;
	die;
}

$out->setDir($dir);
echo $out;
}catch(Exception $ex){
	header("{$_SERVER['SERVER_PROTOCOL']} 500 Internal Server Error");
	echo 'Exception: '.$ex->getMessage();
}