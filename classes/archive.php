<?php
/*
 * Copyright 2016 Pavel Khoroshkov
 */
/**
 * archive
 * @package Filger
 * @author Pavel Khoroshkov aka pgood
 * @link http://pgood.space/
 */
class archive{
	protected static $arExt = array('zip' => 'zip','gz' => 'zlib','tgz' => 'zlib','tar' => false,'rar' => 'rar');
	protected $file,$error,$zip;
	function __construct($file){
		$this->file = $file;
	}
	function zip($dir,$arItems = false){
		$this->closeZip();
		$res = $this->addZipItems($dir,null,$arItems);
		$this->closeZip();
		return $res;
	}
	protected function addZipItems($dir,$zipPath,$arItems = false){
		$res = true;
		if(strlen($zipPath) && substr($zipPath,-1) != '/')
			$zipPath = $zipPath.'/';
		if($arItems === false){
			$arItems = array();
			foreach(scandir($dir) as $item)
				$arItems[] = $item;
		}
		foreach($arItems as $item){
			if($item == '.' || $item == '..') continue;
			$entry = $dir.'/'.$item;
			if(is_dir($entry) && !($res = $this->addZipItems($entry,$zipPath.$item)))
				break;
			if(is_file($entry) && !($res = $this->addZipEntry($entry,$zipPath)))
				break;
		}
		return $res;
	}
	protected function addZipEntry($v,$zipPath = false){
		if(!$this->zip){
			$this->zip = new ZipArchive();
			$this->error = self::getZipOpenErrorMessage($this->zip->open($this->file,ZipArchive::CREATE));
			if($this->error)
				return false;
		}
		if($zipPath === false){
			$res = $this->zip->addFile($v);
		}else{
			$file = pathinfo($v,PATHINFO_BASENAME);
			$zipPath = $zipPath.(substr($zipPath,-1) == '/' ? null : '/');
			$res = isWindows()
				? $this->zip->addFile($v,iconv('UTF-8',DOS_CP.'//IGNORE',fdec($zipPath.$file)))
				: $this->zip->addFile($v,$zipPath.$file);
		}
		if(!$res)
			$this->error = str_replace('%ENTRY%',$v,_('Failed adding ZIP entry %ENTRY%'));
		return $res;
	}
	function closeZip(){
		if($this->zip){
			@$this->zip->close();
			$this->zip = null;
		}
	}
	function extractTo($destination){
		if((is_file($this->file) || !($this->error = _('Archive file not found')))
			&& (self::isExtractable($this->file) || !($this->error = _('Unsuppurted archive type')))
		){
			$ext = strtolower(pathinfo($this->file,PATHINFO_EXTENSION));
			switch($ext){
				case 'zip':
					return $this->extractZipTo($destination);
				case 'gz':
					return $this->extractGzipTo($destination);
				case 'tar':
					return $this->extractTarTo($destination);
				case 'tgz':
					return $this->extractTgzTo($destination);
				case 'rar':
					return $this->extractRarTo($destination);
			}
		}
	}
	protected function extractZipTo($destination){
		$this->error = null;
		$mesExtractFailed = _('Extract failed');
		return $this->isExtesionLoaded('zip')
			&& $this->checkFileExt($this->file,'zip')
			&& $this->isWritableDir($destination)
			&& ($zip = new ZipArchive)
			&& !($this->error = self::getZipOpenErrorMessage($zip->open($this->file)))
			&& (
					(isWindows()
						&& ($zip->extractTo($tmpDir = TEMP_DIR.'zip-'.uniqid()) || !($this->error = $mesExtractFailed))
						&& ($this->renameExtractedZipFiles($tmpDir,$destination) || !($this->error = _('Rename failed')))
						&& (removeDir($tmpDir) || !($this->error = _('Temp dir remove failed')))
					)
					|| ($zip->extractTo($tmpDir = $destination) || !($this->error = $mesExtractFailed))
				)
			&& ($zip->close() || !($this->error = _('Zip hasn`t been closed')));
	}
	protected function renameExtractedZipFiles($path,$pathTarg){
		if(!file_exists($path)) return true;
		if(substr($pathTarg,-1) != '/')
			$pathTarg.= '/';
		if(is_file($path))
			return rename($path,$pathTarg.fenc(iconv(DOS_CP.'//IGNORE','utf-8',pathinfo($path,PATHINFO_BASENAME))));
		if(substr($path,-1) != '/')
			$path.= '/';
		foreach(scandir($path) as $name){
			if($name == '.' || $name == '..') continue;
			if(is_file($path.$name))
				$this->renameExtractedZipFiles($path.$name,$pathTarg);
			elseif(is_dir($path.$name) && mkdir($pathTargNext = $pathTarg.fenc(iconv(DOS_CP.'//IGNORE','utf-8',$name))))
				$this->renameExtractedZipFiles($path.$name,$pathTargNext);
		}
		return true;
	}
	protected function extractGzipTo($destination){
		$this->error = null;
		if($this->isExtesionLoaded('zlib')
			&& $this->checkFileExt($this->file,array('gz','tgz'))
			&& $this->isWritableDir($destination)
		){
			try{
				$p = new PharData($this->file);
				$p->decompress();
				return true;
			}catch(Exception $ex){
				$this->error = str_replace('%ERR_MESSAGE%',$ex->getMessage(),_('Gzip decompress error: %ERR_MESSAGE%'));
			}
		}
	}
	protected function extractTarTo($destination,$file = null){
		$this->error = null;
		$file = $file ? $file : $this->file;
		if($this->checkFileExt($file,'tar')
			&& $this->isWritableDir($destination)
		){
			try{
				$p = new PharData($file);
				return $p->extractTo($destination,null,true);
			}catch(Exception $ex){
				$this->error = str_replace('%ERR_MESSAGE%',$ex->getMessage(),_('Gzip decompress error: %ERR_MESSAGE%'));
			}
		}
	}
	protected function extractTgzTo($destination){
		if((preg_match('/^(.*\.)tgz$/i',$this->file,$m) || !($this->error = 'Incorrect file name'))
			&& $this->extractGzipTo($destination)
			&& (is_file($tarFile = $m[1].'tar') || !($this->error = 'Tar file not found'))
		){
			$res = $this->extractTarTo($destination,$tarFile);
			unlink($tarFile);
			return $res;
		}
	}
	protected function extractRarTo($destination){
		$res = false;
		try{
			if($this->isExtesionLoaded('rar')
				&& $this->checkFileExt($this->file,'rar')
				&& $this->isWritableDir($destination)
				&& (($rar = RarArchive::open($this->file)) || ($this->error = _('Failed opening RAR file')))
				&& (($list = $rar->getEntries()) || ($this->error = _('Failed fetching RAR entries')))
			){
				$res = true;
				foreach($list as $entry)
					if(!$entry->extract($destination)){
						$this->error = str_replace('%ENTRY%',$entry->getName(),_('Failed extracting RAR entry %ENTRY%'));
						$res = false;
						break;
					}
				$rar->close();
			}
		}catch(Exception $ex){
			$this->error = str_replace('%ENTRY%',$ex->getMessage(),_('RAR error: %ERROR%'));
			$res = false;
		}
		return $res;
	}
	function getError(){
		return $this->error;
	}
	protected function isWritableDir($path){
		return (is_dir($path) || !($this->error = _('Destination folder isn`t exists')))
			&& (is_writable($path) || !($this->error = _('Destination folder isn`t writable')));
	}
	protected function checkFileExt($path,$ext){
		if(!is_array($ext))
			$ext = array($ext);
		return in_array(strtolower(pathinfo($path,PATHINFO_EXTENSION)),$ext) || !($this->error = _('Incorrect file name'));
	}
	protected function isExtesionLoaded($ext){
		return extension_loaded($ext) || !($this->error = str_replace('%EXT_NAME%',$ext,_('%EXT_NAME% extenstion isn`t loaded')));
	}
	static function isExtractable($file){
		$ext = strtolower(pathinfo($file,PATHINFO_EXTENSION));
		return isset(self::$arExt[$ext]) && (!self::$arExt[$ext] || extension_loaded(self::$arExt[$ext]));
	}
	protected static function getZipOpenErrorMessage($code){
		if($code === true)
			return false;
		switch($code){
			case ZipArchive::ER_EXISTS:
				return _('File already exists');
			case ZipArchive::ER_INCONS:
				return _('Zip archive inconsistent');
			case ZipArchive::ER_INVAL:
				return _('Invalid argument');
			case ZipArchive::ER_MEMORY:
				return _('Malloc failure');
			case ZipArchive::ER_NOENT:
				return _('No such file.');
			case ZipArchive::ER_NOZIP:
				return _('Not a zip archive');
			case ZipArchive::ER_OPEN:
				return _('Can`t open file');
			case ZipArchive::ER_READ:
				return _('Read error');
			case ZipArchive::ER_SEEK:
				return _('Seek error');
		}
	}
}
