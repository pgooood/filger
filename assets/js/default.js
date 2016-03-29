
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
function uploadProgressBar(){
	var $e = $('.progress-bar');
	this.start = function(){
		this.progress(0);
	};
	this.stop = function(){
		this.progress(0);
	};
	this.progress = function(percent){
		$e.css({width:percent+'%',opacity:percent == 0 ? 0 : 1});
		$e.attr('aria-valuenow',percent);
	};
	this.stop();
};
function getActiveEditor(){
	return parent && parent.tinyMCE ? parent.tinyMCE.activeEditor : null;
};
function dirList(){
	var obj=this
		,upb = new uploadProgressBar();
	this.sort='name';
	this.load=function(url,callback){
		upb.progress(100);
		if(this.$table)
			this.$table.floatThead('destroy');
		$('#output').xslt({
			xmlUrl:url
			,xslUrl:'xsl.php?name=dir'
			,xmlCache:false
			,xslCache:true
			,callback:function(){
				upb.progress(0);
				window.setTimeout(function(){obj.init();},100);
			}
		});
	};
	this.reload=function(){
		this.load('?xml=1&path=.');
	};
	this.message=function(v){
		var editor = getActiveEditor();
		if(editor)
			editor.windowManager.alert(v);
		else alert(v);
	};
	this.confirm=function(message,callback){
		var editor = getActiveEditor();
		if(editor)
			editor.windowManager.confirm(message,callback);
		else callback(window.confirm(message));
	};
	this.reset=function(state){
		$('input[name="file"],input[name="dir"]')
			.prop('checked',state)
			.change();
	};
	this.$getItems=function(){
		return $('input[name="file"],input[name="dir"]');
	};
	this.$getSelectedItems=function(){
		return $('input[name="file"]:checked,input[name="dir"]:checked');
	};
	this.$getSelectedFolders=function(){
		return $('input[name="dir"]:checked');
	};
	this.$getSelectedFiles=function(){
		return $('input[name="file"]:checked');
	};
	this.getSelectedPath=function(){
		return window.dirUrl+$('input[name="file"]:checked').get(0).value;
	};
	//call when file or folder checked/unchecked
	this.checkedState=function(isAnyItemChecked){
		$('#remove,#rename').prop('disabled',!isAnyItemChecked);
	};
	this.resort=function(headerDiv){
		var $e = $(headerDiv)
			,col = $e.data('col')
			,dir = obj.sort == col && obj.dir == 'asc' ? 'desc' : 'asc';
		obj.load('?action=sort&xml=1&col='+col+'&dir='+dir);
		obj.sort = col;
		obj.dir = dir;
	};
	this.init=function(){
		this.$table = $('table#dir');
		
		this.$table.floatThead({
			scrollContainer:function($table){
				return $table.closest('.wrapper');
			}
		});
		
		//init column headers
		this.$table
			.floatThead('getRowGroups')
			.filter('thead')
			.find('th > div')
			.click(function(){obj.resort(this);return false;})
			.addClass('sort')
			.filter('div[data-col="'+obj.sort+'"]')
			.find('.sort-icon')
			.addClass('glyphicon glyphicon-arrow-'+(obj.dir == 'asc' ? 'down' : 'up'));
		
		this.checkedState(false);
		/* path click */
		$('.path a').click(function(){
			obj.load('?xml=1&path='+encodeURIComponent($(this).data('path')));
			return false;
		});
		/* folder click */
		$('table#dir td.dir label').click(function(){
			var name = $('#'+$(this).attr('for')).val();
			obj.load('?xml=1&path='+encodeURIComponent(name));
			return false;
		});
		/* select all change */
		$('input.select_all').change(function(){
			obj.reset(this.checked);
			obj.checkedState(this.checked);
		});
		/* all items change */
		this.$getItems().change(function(){
			obj.checkedState(obj.$getSelectedItems().length);
			if(this.checked)$(this).parents('.item').addClass('warning');
			else $(this).parents('.item').removeClass('warning');
		});
	};
	this.checkFileName=function(name){
		return (name+'').match(/[^\/?*:;{}\\]+/);
	};
	this.getMessage=function(name){
		if(dirListMessages)
			return dirListMessages[name];
	};
	this.init();
};

$(function(){
	if(window.frameElement)
		$('.wrapper').height($(window.frameElement).height() - $('.progress').outerHeight() - $('.toolbar').outerHeight());
	
	var upb = new uploadProgressBar()
		,dl = new dirList()
		,editor = getActiveEditor()
		,showErrorMessage = function(v){ dl.message(v); };

	$('#fileupload').css('opacity',0);
	$('#fileupload').fileupload({
		dataType:'json'
		,limitMultiFileUploads:10
		,start:function(e){
			upb.start();
		}
		,done:function(e,data){
			var errors = [];
			$(data.result.files).each(function(){
				if(this.error)
					errors.push(this.name+': '+this.error);
			});
			if(errors.length)
				dl.message(errors.join(', '));
			upb.stop();
			dl.reload();
		}
		,fail:function(e,data){
			console.log('fail');
			console.log(data);
			upb.stop();
		}
		,progressall:function(e,data){
			upb.progress(parseInt(data.loaded/data.total*100,10));
		}
		,formData:[
			{name:'action',value:'upload'}
		]
	});

	$('#remove').click(function(){
		dl.confirm(dl.getMessage('remove_confirm'),function(res){
			if(!res)return;
			var arFile=[],arDir=[],data={};
			dl.$getSelectedFiles().each(function(){
				if(this.value)arFile.push(this.value);
			});
			dl.$getSelectedFolders().each(function(){
				if(this.value)arDir.push(this.value);
			});
			if(arFile.length||arDir.length){
				if(arFile.length)data['file']=arFile;
				if(arDir.length)data['dir']=arDir;
				$.post('?action=remove',data,function(result){
					dl.reload();
				});
			}else{
				dl.message(dl.getMessage('remove_nothing_selected'));
			};
		});
	});

	$('#new_folder').click(function(){
		var name=window.prompt(dl.getMessage('new_folder_prompt'),dl.getMessage('new_folder_def_name'));
		$.get('?action=new_folder&name='+encodeURIComponent(name),function(xml){
			console.log(xml);
			var $e = $(xml).find('message')
				,type = $e.attr('type');
			if(type == 'error')
				showErrorMessage($e.text());
			else if(type != 'success')
				showErrorMessage(dl.getMessage('new_folder_error'));
			else
				dl.reload();
		});
	});
	
	$('#rename').click(function(){
		var $eDir = dl.$getSelectedFolders()
			,isDir = !!$eDir.length
			,$e = isDir ? $eDir : dl.$getSelectedFiles();
		if(!$e.length){
			showErrorMessage(dl.getMessage('rename_nothing_selected'));
			return;
		};
		var name=window.prompt(dl.getMessage('rename_prompt'),$e.val());
		$.get('?'
			,{
				action:'rename_'+(isDir ? 'dir' : 'file')
				,old:$e.val()
				,'new':name
			}
			,function(xml){
				var $e = $(xml).find('message')
					,type = $e.attr('type');
				if(type == 'error')
					showErrorMessage($e.text());
				else if(type != 'success')
					showErrorMessage(dl.getMessage('rename_error'));
				else
					dl.reload();
			}
			,'xml'
		);
	});
	
	if(editor){
		$('#ok').click(function(){
			var args = editor.windowManager.getParams();
			args.window.document.getElementById(args.input).value = dl.getSelectedPath();
			editor.windowManager.close();
		});
		$('#cancel').click(function(){
			editor.windowManager.close();
		});
	};

});
