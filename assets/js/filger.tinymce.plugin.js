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
		tinymce.activeEditor.windowManager.openUrl(
			{
				title: opt.title || 'Filger'
				,url: editor.settings.external_plugins.filger.replace('filger.tinymce.plugin.js','../../')
				,width: opt.width || 750
				,height: opt.height || 550
				,onMessage: function(dialogApi,details){
					switch(details.data.name){
						case 'selectionChange':
							opt.selectedPath = details.data.path;
							break;
					}
				}
				,onAction: function(dialogApi,details){
					switch(details.name){
						case 'ok':
							callback(opt.selectedPath);
							tinymce.activeEditor.windowManager.close();
							break;
					}
				}
				,buttons:[{
					type: 'custom'
					,text: opt.okButton || 'Ok'
					,name: 'ok'
					,primary: true
				},{
					type: 'cancel'
					,text:  opt.cancelButton || 'Cancel'
				}]
			}
		);
	};
});
