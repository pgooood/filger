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
 * xmlDir
 * @package Filger
 * @author Pavel Khoroshkov aka pgood
 * @link http://pgood.ru/
 */
class xmlDir extends \pgood\xml\xml{
	protected $orderCol;
	protected $orderDir;
	function __construct($path,$displayPath,$orderCol,$orderDir,$isRoot = false){
		parent::__construct();
		$this->append('dir');
		$this->de()->displayPath = $displayPath;
		$this->de()->orderCol = $orderCol;
		$this->de()->orderDir = $orderDir;
		$this->de()->path = $path.(substr($path,-1) == '/' ? null : '/');
		$this->de()->url = dirname($_SERVER['PHP_SELF']).'/'.$this->de()->path;
		$this->initDir($isRoot);
	}
	
	function initDir($isRoot){
		$e = $this->de();
		while($e->firstChild) $e->removeChild($e->firstChild);
		if(is_dir($e->path)
			&& ($d = dir($e->path))
		){
			$arDirs = array();
			$arFiles = array();
			while(false !== ($entry = $d->read())){
				if($entry == '.' || ($isRoot && $entry == '..'))
					continue;
				if(is_dir($path = $e->path.'/'.$entry)) $arDirs[$entry] = $path;
				else{
					switch($this->de()->orderCol){
						case 'ext':
							$arFiles[$entry] = strtolower(pathinfo($path,PATHINFO_EXTENSION));
							break;
						case 'size':
							$arFiles[$entry] = filesize($path);
							break;
						case 'date':
							$arFiles[$entry] = filemtime($path);
							break;
						default:
							$arFiles[$entry] = $entry;
					}
				}
			}
			$d->close();

			asort($arDirs);
			foreach($arDirs as $entry => $path){
				$ed = $e->append('dir');
				$ed->text($entry);
			}

			if($this->de()->orderDir=='desc')
				arsort($arFiles);
			else
				asort($arFiles);
			foreach($arFiles as $entry => $tmp){
				$path = $e->path.'/'.$entry;
				$ef = $e->append('file');
				$ef->text($entry);
				$ef->name = pathinfo($path,PATHINFO_FILENAME);
				$ef->ext = strtolower(pathinfo($path,PATHINFO_EXTENSION));
				$ef->size = formatFileSize(filesize($path));
				$ef->date = date(DATE_FORMAT,filemtime($path));
			}
		}
	}
}