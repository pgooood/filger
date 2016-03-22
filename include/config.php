<?php
define('UPLOAD_ROOT_PATH'	,'userfiles/');
define('XSL_PATH'			,'assets/xml/');
define('FILE_UPLOAD_DIR'	,'assets/jQuery-File-Upload/');
define('LOCALE'				,'en_US');
define('DATE_FORMAT'		,'d.m.Y H:i');
define('DEC_POINT'			,'.');
define('THOUSANDS_SEP'		,',');
define('SESSION_PEFIX'		,'filger_');
define('LOCALE_PATH'		,'./locale');
define('TEXT_DOMAIN'		,'default');

$arFileUploadOptions = array(
	// The maximum number of files for the upload directory
	'max_number_of_files'	=> 999
	// Defines which files (based on their names) are accepted for upload
	,'accept_file_types'	=> '/.+\.(jpe?g|png|gif|pdf|docx?|xlsx?|txt|zip)$/i'
	// The empty image version key defines options for the original image
	,'image_versions'		=> array()
	// The php.ini settings upload_max_filesize and post_max_size
	// take precedence over the following max_file_size setting:
	,'max_file_size'		=> 204800 //200Kb
);