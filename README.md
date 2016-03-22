# Filger
Filger is a PHP plugin for TinyMCE that helps you upload and manage your files on website.  [Demo page](http://pgood.ru/filger/)

## Installation
### Requirements
PHP version of at least 5.3 is required for Filger to work properly. As for other PHP extensions, XSL and Gettext are required.

### Basic Installation
1. Unpack the zip file and upload it to your web server. For example: /www/filger/
2. Open /www/filger/include/config.php in your favorite text editor and configure it exactly as you might want it.
3. Open the Filger in a browser. For example: http://localhost/filger/.
4. Filger offers you download jQuery File Upload. Follow the instructions in the installer, it will download and unzip the jQuery File Upload archive for you.

You can translate Filger to your language. Copy /www/filger/locale/ru_RU/LC_MESSAGES/default.po to /www/filger/locale/your_locale/LC_MESSAGES/default.po and translate it with [Poedit](https://poedit.net/) then change language in include/config.php.

## Usage
```js
tinymce.init({
	selector: 'textarea.tinymce'
	,height: 250
	,plugins: ['link image code']
	,toolbar: 'link image | code'
	,file_browser_callback:function(field,url,type,win){
		var dialog = tinyMCE.activeEditor.windowManager.open({
			url: '<filger url>?type='+type+'&field='+field
			,width: 800
			,height: 350
			,title: 'Filger'
			,resizable: true
		},{
			window: win
			,input: field
			,getWin: function(){return dialog;}
		});
	}
});
```