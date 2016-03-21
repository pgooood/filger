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
/**
 * out
 * @package Filger
 * @author Pavel Khoroshkov aka pgood
 * @link http://pgood.ru/
 */
class out extends \pgood\xml\xml{
	function __construct(){
		parent::__construct('assets/xml/page.xml');
		$this->de()->title = _('Filger');
		$this->de()->append($this->create('script',null,'var dirListMessages='.json_encode(array(
			'remove_confirm' => _('Do you want remove selected files?')
			,'remove_nothing_selected' => _('First select some files or folder')
			,'rename_nothing_selected' => _('Nothing selected')
			,'rename_prompt' => _('New name')
			,'rename_error' => _('Unknown Error!')
			,'new_folder_prompt' => _('Folder name')
			,'new_folder_def_name' => _('Folder')
			,'new_folder_error' => _('Unknown Error!')
		))));
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
		if($tpl = getTemplate('index',array('dir')))
			return $tpl->transform($this);
	}
}