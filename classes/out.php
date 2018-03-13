<?php
/*
 * Copyright 2018 Pavel Khoroshkov
 */
/**
 * out
 * @package Filger
 * @author Pavel Khoroshkov aka pgood
 * @link http://pgood.space/
 */
class out extends \pgood\xml\xml{
	protected $messages;
	function __construct(){
		parent::__construct('page');
		$this->de()->title = _('Filger');
		$this->de()->fileUploaderPath = FILE_UPLOADER_PATH;
		$mesNothingSelected = _('Nothing selected');
		$mesUnknownError =  _('Unknown Error!');
		$this->messages = array(
			'remove_confirm' => _('Do you want remove selected files?')
			,'remove_nothing_selected' => _('First select some files or folder')
			,'rename_nothing_selected' => $mesNothingSelected
			,'rename_prompt' => _('New name')
			,'rename_error' => $mesUnknownError
			,'new_folder_prompt' => _('Folder name')
			,'new_folder_def_name' => _('Folder')
			,'new_folder_error' => $mesUnknownError
			,'extract_confirm' => _('Extract %FILE_NAME% to current folder?')
			,'extract_nothing_selected' => $mesNothingSelected
			,'extract_error' => _('Unknown Error!')
			,'zip_nothing_selected' => $mesNothingSelected
			,'zip_prompt' => _('Enter zip file name')
			,'zip_error' => $mesUnknownError
			,'file_name_error' => _('Please enter valid file name')
			,'download_nothing_selected' => $mesNothingSelected
			,'ok_nothing_selected' => $mesNothingSelected
			,'Name' => _('Name')
			,'Ext' => _('Ext')
			,'Size' => _('Size')
			,'Date' => _('Date')
			,'Download' => _('Download')
			,'CreateFolder' => _('Create Folder')
			,'Rename' => _('Rename')
			,'Remove' => _('Remove')
			,'Ok' => _('Ok')
			,'Cancel' => _('Cancel')
		);
		$this->de()->append($this->create('script',null,'var dirListMessages='.json_encode($this->messages)));
	}
	function installRequired(){
		return !$this->de() || !$this->de()->fileUploaderPath || !is_dir($this->de()->fileUploaderPath);
	}
	function setDir(xmlDir $dir){
		$this->de()->append($dir);
	}
	function __toString(){
		if($this->de()->debug)
			$this->save('temp.xml');
		$mode = $this->evaluate('string(/*/dir/@mode)');
		if($tpl = getTemplate('index'))
			return $tpl->transform($this);
	}
	function alert($v){
		$this->de()->append('message')->text($v);
	}
	function getMessage($name){
		if(isset($this->messages[$name]))
			return $this->messages[$name];
	}
}