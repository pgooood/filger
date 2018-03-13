# Filger
Filger is a TinyMCE file manager/uploader for LAMP platforms. [Demo page](http://pgood.space/filger/)

## Installation
### Requirements
PHP 5.6 or higher is required for Filger to work properly. As for other PHP extensions, XSL and Gettext are required.

### Basic Installation
1. Unpack the zip file and upload it to your web server. For example: /www/filger/
2. Open /www/filger/assets/xml/config.xml in your favorite text editor and configure it exactly as you might want it.
3. Open the Filger in a browser. For example: http://localhost/filger/.
4. Filger offers you download jQuery File Upload. Follow the instructions in the installer, it will download and unzip the jQuery File Upload archive for you.

You can translate Filger to your language. Copy /www/filger/locale/ru_RU/LC_MESSAGES/default.po to /www/filger/locale/your_locale/LC_MESSAGES/default.po and translate it with [Poedit](https://poedit.net/) then change language in include/config.php.

## Usage
```js
tinymce.init({
	selector: 'textarea.tinymce'
	,height: 300
	,plugins: ['link image code']
	,toolbar: 'link image | code'
	,external_plugins: {
		'filger': '<filger url>/assets/js/filger.tinymce.plugin.js'
	}
});
```

## Support
I am happy to provide support, so if you need a hand fill in feedback form at http://pgood.space/#feedback-form or use Github Issues https://github.com/pgooood/filger/issues