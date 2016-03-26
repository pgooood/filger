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
require_once 'include/config.php';
require_once 'include/lib.php';

if(!defined('DIRECTORY_SEPARATOR'))
	define('DIRECTORY_SEPARATOR',empty($_SERVER['WINDIR']) ? '/' : '\\');
	
if(!session_id())
	session_start();

initGettext(LOCALE,LOCALE_PATH,TEXT_DOMAIN);

try{
$response = new xmlResponse();
$out = new out();
$action = $out->installRequired() ? 'install' : param('action');
$spath = new sessionPath(SESSION_PEFIX.'path');
$currentPath = $spath->relate(UPLOAD_ROOT_PATH);
$currentUrl = $spath->relate(UPLOAD_ROOT_PATH);

switch($action){
	case 'sort':
		$_SESSION[SESSION_PEFIX.'order_col'] = param('col');
		$_SESSION[SESSION_PEFIX.'order_dir'] = param('dir');
		break;
	case 'upload':
		require_once($out->de()->fileUploaderPath.'server/php/UploadHandler.php');
		if(!is_array($arFileUploadOptions))
			$arFileUploadOptions = array();
		$arFileUploadOptions['upload_dir'] = $currentPath;
		$arFileUploadOptions['upload_url'] = $currentUrl;
		new UploadHandler($arFileUploadOptions,true,array(
				1 => _('The uploaded file exceeds the upload_max_filesize directive in php.ini')
				,2 => _('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form')
				,3 => _('The uploaded file was only partially uploaded')
				,4 => _('No file was uploaded')
				,6 => _('Missing a temporary folder')
				,7 => _('Failed to write file to disk')
				,8 => _('A PHP extension stopped the file upload')
				,'post_max_size' => _('The uploaded file exceeds the post_max_size directive in php.ini')
				,'max_file_size' => str_replace('%MAX_FILE_SIZE%'
									,empty($arFileUploadOptions['max_file_size']) ? 'unlimited' : formatFileSize($arFileUploadOptions['max_file_size'])
									,_('File is too big, max file size is %MAX_FILE_SIZE%'))
				,'min_file_size' => _('File is too small')
				,'accept_file_types' => _('Filetype not allowed')
				,'max_number_of_files' => _('Maximum number of files exceeded')
				,'max_width' => _('Image exceeds maximum width')
				,'min_width' => _('Image requires a minimum width')
				,'max_height' => _('Image exceeds maximum height')
				,'min_height' => _('Image requires a minimum height')
				,'abort' => _('File upload aborted')
				,'image_resize' => _('Failed to resize image')
			));
		die;
	case 'remove':
		$ar = array();
		if(isset($_REQUEST['dir']) && is_array($_REQUEST['dir']))
			$ar = array_merge($ar,$_REQUEST['dir']);
		if(isset($_REQUEST['file']) && is_array($_REQUEST['file']))
			$ar = array_merge($ar,$_REQUEST['file']);
		foreach($ar as $name)
			if(!removeDir($currentPath.$name))
				$response->error('fail');
			$response->success('ok');
		break;
	case 'rename_dir':
		if(isValidFileName($oldName = param('old'))
			&& ($newName = trim(param('new')))
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
		if(isValidFileName($oldName = param('old'))
			&& ($newName = trim(param('new')))
			&& is_file($oldPath = $currentPath.$oldName)
		){
			if(isValidFileName($newName)){
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
		if(isValidFileName($name = param('name'))){
			if(mkdir($currentPath.$name))
				$response->success(_('Directory created'));
			else
				$response->error(_('Error! Directory hasn\'t been created.'));
		}else
			$response->error(_('Invalid name'));
		break;
	case 'install':
		require_once 'include/install.php';
		die;
	default:
		$arPath = array_filter(explode('/',param('path')));
		//vdump($arPath,1);
		foreach($arPath as $dir)
			$spath->change($dir);
}

$dir = new xmlDir($spath
		,isset($_SESSION[SESSION_PEFIX.'order_col']) ? $_SESSION[SESSION_PEFIX.'order_col'] : null
		,isset($_SESSION[SESSION_PEFIX.'order_dir']) ? $_SESSION[SESSION_PEFIX.'order_dir'] : null
	);
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
	//echo 'Exception: '.$ex->getMessage().'<pre>'.$ex->getTraceAsString().'</pre>';
}