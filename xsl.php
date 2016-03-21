<?php
require_once 'include/config.php';
require_once 'include/lib.php';

initGettext(LOCALE,LOCALE_PATH,TEXT_DOMAIN);

if($tpl = getTemplate(param('name'))){
	header('Content-type: text/xml');
	echo $tpl;
}