<?php
/*
 * Copyright 2018 Pavel Khoroshkov
 */
/**
 * xmlDir
 * @package Filger
 * @author Pavel Khoroshkov aka pgood
 * @link http://pgood.space/
 */
class xmlDir extends \pgood\xml\xml{
	protected $orderCol,$orderDir,$arIcons,$path,$page,$pageSize;
	function __construct(sessionPath $spath,$orderCol,$orderDir,$mode,$page){
		parent::__construct();
		$this->path = $spath->relate(UPLOAD_ROOT_PATH);
		$this->page = intval($page) ? intval($page) : 1;
		$this->pageSize = 100;
		$this->append('dir');
		$de = $this->de();
		$de->orderCol = $orderCol ? $orderCol : 'name';
		$de->orderDir = $orderDir ? $orderDir : 'asc';
		$de->url = fdec($spath->relate(UPLOAD_ROOT_URL));
		$de->path = fdec($spath->relate(UPLOAD_ROOT_PATH));
		$de->mode = $mode ? $mode : 'list';
		$this->initDir($spath->isRoot());
		$arPath = $spath->getArray();
		$ePath = $this->de()->append('path');
		foreach($arPath as $dir)
			$ePath->append('item')->text($dir);
		
	}
	function getIcon($ext){
		if(!isset($this->arIcons[$ext]))
			$this->arIcons[$ext] = is_file('assets/images/ico16/'.$ext.'.png');
		return $this->arIcons[$ext] ? $ext : '_';
	}
	function initDir($isRoot){
		$e = $this->de();
		while($e->firstChild) $e->removeChild($e->firstChild);
		if(is_dir($this->path)
			&& ($d = dir($this->path))
		){
			$arDirs = array();
			$arFiles = array();
			while(false !== ($entry = $d->read())){
				if(is_dir($path = $this->path.'/'.$entry)){
					if($entry != '.' &&  $entry != '..')
						$arDirs[$entry] = $path;
				}else{
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

			//sort
			if($this->de()->orderDir=='desc'){
				arsort($arDirs);
				arsort($arFiles);
			}else{
				asort($arDirs);
				asort($arFiles);
			}
			if(!$isRoot)
				$arDirs = array_merge(array('..' => $this->path.'/..'),$arDirs);
			
			$arItems = array_merge(array_keys($arDirs),array_keys($arFiles));
			
			//pagination
			$len = count($arItems);
			$pages = ceil($len / $this->pageSize);

			if($this->page > $pages)
				$this->page = $pages;
			elseif($this->page <= 0)
				$this->page = 1;

			if($len > $this->pageSize)
				$arItems = array_slice($arItems,$this->pageSize * ($this->page - 1),$this->pageSize);

			$counter = 0;			
			foreach($arItems as $entry){
				if(is_dir($path = $this->path.'/'.$entry)){
					$e->append('dir')->text(fdec($entry));
				}else{
					$path = $this->path.$entry;
					if($name = pathinfo($path,PATHINFO_FILENAME)){
						
						$name = \ForceUTF8\Encoding::toUTF8($name);
						$fullName = \ForceUTF8\Encoding::toUTF8($entry);

						$ef = $e->append('file');
						$ef->text(fdec($fullName));
						$ef->name = fdec($name);
						$ef->ext = strtolower(pathinfo($path,PATHINFO_EXTENSION));
						$ef->size = formatFileSize(filesize($path));
						$ef->date = date(DATE_FORMAT,filemtime($path));
						$ef->ico = $this->getIcon($ef->ext);
						$ef->img = !!preg_match('/(jpe?g|png|gif)/i',$ef->ext);
						$ef->arch = !!archive::isExtractable($entry);
						$counter++;
					}
				}
			}
			$this->de()->items = $len;
			$this->de()->pages = $pages;
			$this->de()->page = $this->page;
			//die;
		}
	}
}