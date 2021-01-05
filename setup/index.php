<?php
/*
 * Copyright 2016 Pavel Khoroshkov
 */

require_once '../include/config.php';
require_once '../include/lib.php';

function jsonResponse($arResponse){
	header('Content-Type: application/json');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	echo json_encode($arResponse);
	die;
}

checkPHP();

spl_autoload_register(function($class){
	if(is_file($path = '../classes/'.str_replace('\\','/',$class).'.php'))
		include($path);
});

define('ROOT_PATH','../');

$conf = new \pgood\xml\cached(CONFIG_PATH);
$locale = $conf->evaluate('string(/config/locale/lang)');
define('LOCALE',$locale ? $locale : 'en_US');

if(is_file($authPath = $conf->evaluate('string(/config/authenticator)')))
	require_once $authPath;

initGettext(LOCALE,ROOT_PATH.LOCALE_PATH,TEXT_DOMAIN);

$_in = new in;
$out = new \pgood\xml\xml('page');
$tpl = new \pgood\xml\template('assets/xml/default.xsl');
$configUri = 'file:///'.ROOT_PATH.CONFIG_PATH.'?/config';
$fileUploadDownloadUrl = 'https://github.com/blueimp/jQuery-File-Upload/archive/master.zip';

try{
	switch(param('action')){
		case 'ajax_fileupload_status':
			$arResponse = array();
			$scheme = new \pgood\form\xmlScheme;
			if(($path = $scheme->value(new \pgood\form\uri($configUri.'/upload/uploader'))) && is_dir(ROOT_PATH.$path)){
				$arResponse['status'] = 'info';
				$arResponse['html'] = _('installed');
			}else{
				$arResponse['status'] = 'danger';
				$arResponse['html'] = str_replace('%INSTALL_URL%'
					,parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH).'?action=ajax_fileupload_install'
					,_('It has to be installed. Try to <a href="%INSTALL_URL%" class="btn btn-success btn-xs install-link">download and setup</a> it automatically.'));
			}
			jsonResponse($arResponse);
			break;
		case 'ajax_fileupload_install':
			$arResponse = array();
			$scheme = new \pgood\form\xmlScheme;
			if(($path = installFileUpload($fileUploadDownloadUrl,ROOT_PATH.FILE_UPLOAD_DIR))
				&& $scheme->add(new \pgood\form\uri($configUri.'/upload/uploader'),substr($path,strlen(ROOT_PATH)))
			){
				$scheme->save();
				$arResponse['status'] = 'info';
				$arResponse['html'] = _('successfuly installed');
			}else{
				$arResponse['status'] = 'danger';
				$arResponse['html'] = str_replace(array('%DOWNLOAD_URL%','%UNPACK_DIR%')
					,array($fileUploadDownloadUrl,FILE_UPLOAD_DIR)
					,_('An error has occurred while installing jQuery File Upload. You can <a href="%DOWNLOAD_URL%" target="_blank">download</a> and unpack it manually to "<code>%UNPACK_DIR%</code>" directory.'));
			}
			jsonResponse($arResponse);
			break;
		default:
			$out->de()->title = _('Filger Settings');
			$forms = new \pgood\xml\xml('assets/xml/forms.xml');
			if($e = $forms->query('/forms/form[@id="setup"]')->item(0)){
				$e->baseURI = $configUri;
				$form = new \pgood\form\form($e);
				if($ns = $form->query('//php-checker/*'))
					foreach($ns as $e){
						switch($e->name()){
							case 'ext':
								if(extension_loaded('zlib'))
									$e->ok = true;
								break;
							case 'val':
								if(($v = ini_get($e->name)) == $e->equal)
									$e->ok = true;
								else
									$e->text(str_replace('%CURRENT_VALUE%',$v,$e->text()));
								break;
						}
					}
					
				if($form->sent()){
					$form->save();
					if($form->errors())
						$form->error(_('Settings saving failed'));
					else
						$form->success(_('Settings have been successfully saved'));
					header('Location: '.parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));
					die;
				}else
					$form->load();
				$out->de()->append($form);
			}
			$out->save('temp.xml');
	}
	echo $tpl->transform($out);
}catch(\Exception $ex){
	$out = new \pgood\xml\xml('exception');
	$out->de()->text('Exception: '.$ex->getMessage().'<hr><pre>'.$ex->getTraceAsString().'</pre>');
	header("{$_SERVER['SERVER_PROTOCOL']} 500 Internal Server Error");
	echo $tpl->transform($out);
}