/**
 * Filger plugin for TinyMCE
 * 
 * @requires //cdn.tinymce.com/4/tinymce.min.js
 * @author Pavel Khoroshkov
 * @example
 * tinymce.init({
 *		selector: 'textarea',
 *		plugins: ['image anchor filger'],
 *		external_plugins: {'filger': '/filger/js/filger.tinymce.plugin.js'}
 *	});
 */
tinymce.PluginManager.add('filger',function(editor,url){
	editor.settings.file_picker_callback = function(callback,value,meta) {
		var opt = typeof editor.settings.filger == 'object' ? editor.settings.filger : {};
		tinymce.activeEditor.windowManager.open(
			{
				title: opt.title ? opt.title : 'Filger'
				,url: editor.settings.external_plugins.filger.replace('filger.tinymce.plugin.js','../../')
				,width: opt.width ? opt.width : 750
				,height: opt.height ? opt.height : 550
			}
			,{
				oninsert: function(url){
					callback(url);
					tinymce.activeEditor.windowManager.close();
				}
			}
		);
	};
});
