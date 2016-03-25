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
header('Content-Type: text/html; charset=utf-8');
$fileUploadHomePage = 'https://blueimp.github.io/jQuery-File-Upload/';
$fileUploadDownloadUrl = 'https://github.com/blueimp/jQuery-File-Upload/archive/master.zip';
?><html><head><title>Filger</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body><?
if(param('install')){
	$error = true;
	if((!is_dir(FILE_UPLOAD_DIR) || removeDir(FILE_UPLOAD_DIR))
		&& mkdir(FILE_UPLOAD_DIR)
		&& ($h = fopen($fileUploadDownloadUrl,'r'))
		&& file_put_contents($zipPath = FILE_UPLOAD_DIR.'jQuery-File-Upload.zip',$h) !== false
		&& ($zip = new ZipArchive())
		&& $zip->open($zipPath) === true
		&& $zip->extractTo(FILE_UPLOAD_DIR)
	){
		$zip->close();
		if(is_dir($path = FILE_UPLOAD_DIR.'jQuery-File-Upload-master/')){
			$xml = new \pgood\xml\xml('assets/xml/page.xml');
			if(!$xml->de())
				$xml->append($xml->create('page'));
			$xml->de()->fileUploaderPath = $path;
			$error = !$xml->save();
			//remove danger files
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
	if($error){
		echo str_replace(array('%HOME_URL%','%DOWNLOAD_URL%','%UNPACK_DIR%')
			,array($fileUploadHomePage,$fileUploadDownloadUrl,FILE_UPLOAD_DIR)
			,_('An error has occurred while installing <a href="%HOME_URL%" target="_blank">jQuery File Upload</a>. You can <a href="%DOWNLOAD_URL%" target="_blank">download</a> and unpack it manually to "<code>%UNPACK_DIR%</code>" directory.'));
	}else{
		echo str_replace(array('%HOME_URL%','%OK_URL%')
			,array($fileUploadHomePage,parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH))
			,_('<a href="%HOME_URL%" target="_blank">jQuery File Upload</a> successfuly installed. <a href="%OK_URL%">Ok</a>'));
	}
}else{
	echo str_replace(array('%HOME_URL%','%INSTALL_URL%')
		,array($fileUploadHomePage,parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH).'?install=1')
		,_('<a href="%HOME_URL%" target="_blank">jQuery File Upload</a> required. <a href="%INSTALL_URL%">Try to download and setup it automatically</a>.'));
}
?></html></body>