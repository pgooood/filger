<?php
/*
 * Copyright 2016 Pavel Khoroshkov
 */
/**
 * filgerUploadHandler
 * @package Filger
 * @author Pavel Khoroshkov aka pgood
 * @link http://pgood.space/
 */
class filgerUploadHandler extends UploadHandler{
	function __construct($options){
		parent::__construct($options,true,array(
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
	}
	protected function get_upload_path($file_name = null, $version = null){
		if(($res = parent::get_upload_path($file_name,$version))
			&& $file_name
		){
			$res = str_replace($file_name,fenc($file_name),$res);
		}
		return $res;
    }
}
