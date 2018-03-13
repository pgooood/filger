<?php
$maxSize = param('max',FILTER_VALIDATE_INT) ? param('max',FILTER_SANITIZE_NUMBER_INT) : 1600;
$src = param('src',FILTER_SANITIZE_STRING);
$host = parse_url($src,PHP_URL_HOST);
$isUrl = !!$host;
$hAlign = isset($_GET['ha']) ? $_GET['ha'] : 'center';
$vAlign = isset($_GET['va']) ? $_GET['va'] : 'middle';
$w = param('w',FILTER_SANITIZE_NUMBER_INT);
$h = param('h',FILTER_SANITIZE_NUMBER_INT);
$thumbPath = '.thumb/';

if('UTF-8' === mb_detect_encoding($src))
	$src = mb_convert_encoding($src,'windows-1251');

//if (!$src || (!$isUrl && !is_file($src)) || ($isUrl && $host != $_SERVER['HTTP_HOST']))
if (!$src || $isUrl)
	die('file not found');

//проверяем расширение файла и выясняем поддерживает ли PHP формат изображения
$path = $isUrl ? parse_url($src,PHP_URL_PATH) : $src;
$ext = strtolower(pathinfo($path,PATHINFO_EXTENSION));
$image_type = 0x0;
switch($ext){
    case 'gif':
        $image_type |= IMG_GIF;
        break;
    case 'png':
        $image_type |= IMG_PNG;
        break;
    case 'jpg':
    case 'jpeg':
        $image_type |= IMG_JPG;
        break;
}
if(!(imagetypes() & $image_type))
	die('unsupported image type');

//проверяем кэш
$arFile = array(
		'url' => $_SERVER['REQUEST_URI']
		,'size' => filesize($src)
		,'time' => filemtime($src)
	);
$hash = md5(implode('',$arFile));
if(is_file($thumb = $thumbPath.$hash.'.jpg')){
	header('Content-type: image/jpeg');
	echo file_get_contents($thumb);
	die;
}

//очистка
$infoPath = $thumbPath.'.checkdate';
if(!is_file($infoPath)
	|| (time() - intval(file_get_contents($infoPath)) > 604800/*неделя - периодичность чистки*/)
){
	if(is_dir($thumbPath)
		&& ($d = dir($thumbPath))
	){
		while(false !== ($entry = $d->read())){
			if($entry == '.' || $entry == '..' || is_dir($path = $thumbPath.'/'.$entry))
				continue;
			if(time() - filemtime($path) > 604800/*неделя - время жизни превьюшки*/)
				unlink($path);
		}
		$d->close();
		file_put_contents($infoPath,time());
	}
}
	
	
//получаем новые размеры картинки
list($width,$height) = getimagesize($src);

$x_source = $y_source = 0;
if ($w && $h) { //масштабируем по заданным пропорциям
    if ($height * $w / $width < $h) {
        $temp_width = ($height * $w) / $h;
        switch ($hAlign) {
            case 'left':
                $x_source = 0;
                break;
            case 'right':
                $x_source = $width - $temp_width;
                break;
            default:
                $x_source = ($width - $temp_width) / 2;
        }
        $width = $temp_width;
    } elseif ($width * $h / $height < $w) {
        $temp_height = ($width * $h) / $w;
        switch ($vAlign) {
            case 'top':
                $y_source = 0;
                break;
            case 'bottom':
                $y_source = $height - $temp_height;
                break;
            default:
                $y_source = ($height - $temp_height) / 2;
        }
        $height = $temp_height;
    }
} elseif ($w) { //вычисляем высоту по заданной ширине
    $h = $w * $height / $width;
} elseif ($h) { //вычисляем ширину по заданной высоте
    $w = $h * $width / $height;
} elseif ($width >= $height && $width > $maxSize) { //уменьшаем слишком широкие
    $w = $maxSize;
    $h = $w * $height / $width;
} elseif ($width < $height && $height > $maxSize) { //уменьшаем слишком высокие
    $h = $maxSize;
    $w = $h * $width / $height;
} else { //оставляем как есть
    $w = $width;
    $h = $height;
}

//Изменяем и выводим картинку
$image_p = imagecreatetruecolor($w,$h);
$image = null;
switch ($image_type) {
    case IMG_GIF:
        $image = imagecreatefromgif($src);
        break;
    case IMG_PNG:
        $image = imagecreatefrompng($src);
        break;
    case IMG_JPG:
        $image = imagecreatefromjpeg($src);
        break;
}
imagecopyresampled($image_p,$image,0,0,$x_source,$y_source,$w,$h,$width,$height);
header('Content-type: image/jpeg');
imagejpeg($image_p,$thumb,90);
imageDestroy($image_p);
imageDestroy($image);

echo file_get_contents($thumb);

function param($name,$filter = FILTER_DEFAULT){
	if(isset($_GET[$name]))
		return filter_var($_GET[$name],$filter);
}