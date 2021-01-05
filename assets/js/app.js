function getActiveEditor(){
	return parent && parent.tinyMCE ? parent.tinyMCE.activeEditor : null;
};

Vue.component('dir-list',{
	template: `
<div>
	<div id="output" class="wrapper" @scroll="onSCroll">
		<template v-if="mode === 'thumb'">
			<table id="dir-thumbs" class="table dir">
				<thead>
					<tr>
						<th colspan="5" class="pt-0 pb-0">
							<div class="navbar">
								<div class="path">/<span v-for="dir in dirPath">{{ dir }}/</span></div>
								<div class="top-nav">
									<div class="btn-group">
										<button type="button" class="btn btn-light btn-sm" @click="changeMode('list')">
											<span class="material-icons align-middle">list</span>
										</button>
										<button type="button" class="btn btn-light btn-sm active">
											<span class="material-icons align-middle">view_comfy</span>
										</button>
									</div>
								</div>
							</div>
						</th>
					</tr>
					<tr>
						<th scope="col" class="chb">
							<input class="select_all" type="checkbox" @click="onSelectAll">
						</th>
						<th scope="col" class="name">
							<div class="sort" data-col="name">
								<span class="sort-text">{{ $t('message.Name') }}</span>
							</div>
						</th>
						<th scope="col" class="ext">
							<div class="sort" data-col="ext">
								<span class="sort-text">{{ $t('message.Ext') }}</span>
							</div>
						</th>
						<th scope="col" class="size">
							<div class="sort" data-col="size">
								<span class="sort-text">{{ $t('message.Size') }}</span>
							</div>
						</th>
						<th scope="col" class="date">
							<div class="sort" data-col="date">
								<span class="sort-text">{{ $t('message.Date') }}</span>
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					<td colspan="5">
						<div class="container-fluid">
							<div class="row">
								
								<div class="col-6 col-sm-4 col-md-3 col-lg-2 thumb-cell dir" v-for="dir in dirList" :key="dir.id">
									<input type="checkbox" v-show="dir.name != '..'" :id="dir.id" :value="dir.name" v-model="dir.selected" true-value="1" false-value="0" @change="onSelectionChange">
									<label :for="dir.id" @click.prevent="onDirClick">
										<figure class="card">
											<img class="card-img-top ico" :width="thumb.width" :height="thumb.height" style="background-image:url(assets/images/ico32/folder.png)" src="assets/images/blank.png" alt="Folder">
											<figcaption class="card-body"><div class="text-truncate">{{ dir.name }}</div></figcaption>
										</figure>
									</label>
								</div>

								<div class="col-6 col-sm-4 col-md-3 col-lg-2 thumb-cell file" v-for="file in fileList" :key="file.id">
									<input type="checkbox" name="file" :id="file.id" :value="file.name" v-model="file.selected" true-value="1" false-value="0" @change="onSelectionChange">
									<label :for="file.id">
										<figure class="card">
											<template v-if="file.img">
												<img class="card-img-top" :width="thumb.width" :height="thumb.height" :style="{'background-image':file.imgThumbBg}" src="assets/images/blank.png">
											</template>
											<template v-else>
												<img class="card-img-top ico" :width="thumb.width" :height="thumb.height" :style="{'background-image':'url(assets/images/ico32/'+file.ico+'.png)'}" src="assets/images/blank.png">
											</template>
											<figcaption class="card-body"><div class="text-truncate">{{ file.name }}</div></figcaption>
										</figure>
									</label>
								</div>

							</div>
						</div>
					</td>
				</tbody>
			</table>
		</template>


		<template v-else>
			<table id="dir" class="table table-striped dir">
				<thead>
					<tr>
						<th colspan="5" class="pt-0 pb-0">
							<div class="navbar">
								<div class="path">/<span v-for="dir in dirPath">{{ dir }}/</span></div>
								<div class="top-nav">
									<div class="btn-group">
										<button type="button" class="btn btn-light btn-sm active">
											<span class="material-icons align-middle">list</span>
										</button>
										<button type="button" class="btn btn-light btn-sm" @click="changeMode('thumb')">
											<span class="material-icons align-middle">view_comfy</span>
										</button>
									</div>
								</div>
							</div>
						</th>
					</tr>
					<tr>
						<th scope="col" class="chb">
							<input class="select_all" type="checkbox" @click="onSelectAll">
						</th>
						<th scope="col" class="name">
							<div class="sort" data-col="name">
								<template v-if="orderCol === 'name'">
									<span v-if="orderDir === 'desc'" class="material-icons align-middle">keyboard_arrow_down</span>
									<span v-if="orderDir === 'asc'" class="material-icons align-middle">keyboard_arrow_up</span>
								</template>
								<span class="sort-text" @click="onSort">{{ $t('message.Name') }}</span>
							</div>
						</th>
						<th scope="col" class="ext">
							<div class="sort" data-col="ext">
								<template v-if="orderCol === 'ext'">
									<span v-if="orderDir === 'desc'" class="material-icons align-middle">keyboard_arrow_down</span>
									<span v-if="orderDir === 'asc'" class="material-icons align-middle">keyboard_arrow_up</span>
								</template>
								<span class="sort-text" @click="onSort">{{ $t('message.Ext') }}</span>
							</div>
						</th>
						<th scope="col" class="size">
							<div class="sort" data-col="size">
								<template v-if="orderCol === 'size'">
									<span v-if="orderDir === 'desc'" class="material-icons align-middle">keyboard_arrow_down</span>
									<span v-if="orderDir === 'asc'" class="material-icons align-middle">keyboard_arrow_up</span>
								</template>
								<span class="sort-text" @click="onSort">{{ $t('message.Size') }}</span>
							</div>
						</th>
						<th scope="col" class="date">
							<div class="sort" data-col="date">
								<template v-if="orderCol === 'date'">
									<span v-if="orderDir === 'desc'" class="material-icons align-middle">keyboard_arrow_down</span>
									<span v-if="orderDir === 'asc'" class="material-icons align-middle">keyboard_arrow_up</span>
								</template>
								<span class="sort-text" @click="onSort">{{ $t('message.Date') }}</span>
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr class="item" v-for="dir in dirList" :key="dir.id" :class="{ 'table-warning': parseInt(dir.selected) }">
						<td class="chb">
							<input type="checkbox" v-show="dir.name != '..'" :id="dir.id" :value="dir.name" v-model="dir.selected" true-value="1" false-value="0" @change="onSelectionChange">
						</td>
						<td class="dir" colspan="4">
							<div class="c"><div class="w">
								<label :for="dir.id" @click.prevent="onDirClick">{{ dir.name }}</label>
							</div></div>
						</td>
					</tr>
					<tr class="item" v-for="file in fileList" :key="file.id" :class="{ 'table-warning': parseInt(file.selected) }">
						<td class="chb">
							<input type="checkbox" name="file" :id="file.id" :value="file.name" v-model="file.selected" true-value="1" false-value="0" @change="onSelectionChange">
						</td>
						<td class="file">
							<div class="c"><div class="w">
								<label :for="file.id" :style="{'background-image':'url(assets/images/ico16/'+file.ico+'.png'}">{{ file.name }}</label>
							</div></div>
						</td>
						<td class="ext">
							<div class="c"><div class="w">{{ file.ext }}</div></div>
						</td>
						<td class="size">
							<div class="c"><div class="w">{{ file.size }}</div></div>
						</td>
						<td class="date">
							<div class="c"><div class="w">{{ file.date }}</div></div>
						</td>
					</tr>
				</tbody>
			</table>
		</template>
	</div>
	<div class="progress">
		<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" :style="{width:request.percent+'%',opacity:request.percent == 0 ? 0 : 1}"></div>
	</div>
	<nav class="navbar navbar-default toolbar">
		<div class="container-fluid">
			<div class="pull-left leftbar">
				<div class="btn-toolbar">
					<div class="btn-group" role="group">
						<label for="fileupload" class="btn btn-light btn-sm">
							<span class="material-icons">file_upload</span>
							<input id="fileupload" type="file" name="files[]" multiple="multiple" class="d-none">
						</label>
						<button id="download" type="button" class="btn btn-light btn-sm" :disabled="btn.download.disabled" :title="$t('message.Download')" @click="onDownload">
							<span class="material-icons">file_download</span>
						</button>
					</div>
					<div class="btn-group ml-2" role="group">
						<button id="new_folder" type="button" class="btn btn-light btn-sm" :title="$t('message.CreateFolder')" @click="onNewFolder">
							<span class="material-icons">create_new_folder</span>
						</button>
						<button id="rename" type="button" class="btn btn-light btn-sm" :disabled="btn.rename.disabled" :title="$t('message.Rename')" @click="onRename">
							<span class="material-icons">mode_edit</span>
						</button>
						<button id="remove" type="button" class="btn btn-light btn-sm" :disabled="btn.remove.disabled" :title="$t('message.Remove')" @click="onRemove">
							<span class="material-icons">delete</span>
						</button>
					</div>
				</div>
			</div>
		</div>
	</nav>
	
</div>`
	,data: function(){return {
		orderCol: 'name'
		,orderDir: 'asc'
		,url: ''
		,path: ''
		,dirPath: []
		,mode: 'list'
		,items: 0
		,pages: 1
		,page: 1
		,dirList: []
		,fileList: []
		,requestInProgress: false
		,request: {
			inProgress: false
			,percent: 0
		}
		,btn: {
			download: {
				disabled: true
			}
			,new_folder: {
				disabled: false
			}
			,rename: {
				disabled: true
			}
			,remove: {
				disabled: true
			}
		},thumb: {
			width: 200
			,height: 200
		}
	}}
	,methods: {
		onSCroll: function(evt){
			if(!this.request.inProgress && this.page < this.pages){
				var $e = $(evt.target)
					,past = $e.scrollTop() + $e.height()
					,size = $e.prop('scrollHeight');
				if(size - past < 500)
					this.getDir(this.mode,++this.page);
			}
		}
		,onSelectAll: function(evt){
			var state = $(evt.target).prop('checked') ? 1 : 0;
			$(this.dirList).each(function(){
				if(this.name != '..')
					this.selected = state;
			});
			$(this.fileList).each(function(){
				this.selected = state;
			});
			this.checkState();
		}
		,onDirClick: function(evt){
			var dir = $('#'+$(evt.target).closest('label').attr('for')).val();
			if(dir){
				this.dirList = [];
				this.fileList = [];
				this.getDir(this.mode,1,dir);
			}
		}
		,onSelectionChange: function(evt){
			this.checkState();
			window.parent.postMessage({
				mceAction: 'customAction',
				data: {
					name: 'selectionChange'
					,path: this.getSelectedPath()
				}
			},'*');
		}
		,onRemove: function(evt){
			var comp = this;
			this.confirm(comp.$t('message.remove_confirm'),function(res){
				if(!res)return;
				var data={file:[],dir:[]};
				$(comp.getSelectedFiles()).each(function(){
					data.file.push(this.name);
				});
				$(comp.getSelectedDirs()).each(function(){
					data.dir.push(this.name);
				});
				if(data.file.length || data.dir.length){
					$.post('?action=remove',data,function(result){
						comp.reload();
					});
				}else{
					comp.message(this.$t('message.remove_nothing_selected'));
				};
			});
		}
		,onNewFolder: function(evt){
			var comp = this
				,name = window.prompt(this.$t('message.new_folder_prompt'),this.$t('message.new_folder_def_name'));
			if(name)
				$.get('?action=new_folder&name='+encodeURIComponent(name),function(xml){
					var $e = $(xml).find('message')
						,type = $e.attr('type');
					if(type == 'error')
						comp.showErrorMessage($e.text());
					else if(type != 'success')
						comp.showErrorMessage(this.$t('message.new_folder_error'));
					else
						comp.reload();
				});
		}
		,onRename: function(evt){
			var arDir = this.getSelectedDirs()
				,isDir = !!arDir.length
				,arItems = isDir ? arDir : this.getSelectedFiles();
			if(!arItems.length){
				this.showErrorMessage(this.$t('message.rename_nothing_selected'));
				return;
			};
			var comp = this
				,oldName = arItems[0].name
				,name = window.prompt(this.$t('message.rename_prompt'),oldName);
			if(name)$.get('?'
				,{
					action: 'rename_'+(isDir ? 'dir' : 'file')
					,old: oldName
					,'new': name
				}
				,function(xml){
					var $e = $(xml).find('message')
						,type = $e.attr('type');
					if(type == 'error')
						comp.showErrorMessage($e.text());
					else if(type != 'success')
						comp.showErrorMessage(comp.$t('message.rename_error'));
					else
						comp.reload();
				}
				,'xml'
			);
		}
		,onDownload: function(evt){
			var strSearch = 'action=download'
				,arFiles = this.getSelectedFiles()
				,arDirs = this.getSelectedDirs();
			if(arFiles.length || arDirs.length){
				$(arDirs).each(function(){
					strSearch+= '&file[]='+encodeURIComponent(this.name);
				});
				$(arFiles).each(function(){
					strSearch+= '&file[]='+encodeURIComponent(this.name);
				});
				window.location.search = strSearch;
			}else
				this.showErrorMessage(this.$t('message.download_nothing_selected'));
		}
		,onSort: function(evt){
			var col = $(evt.target).closest('.sort').data('col')
				,dir = 'asc';
			if(col){
				if(this.orderCol === col)
					dir = this.orderDir === 'asc' ? 'desc' : 'asc';
				$.get('?xml=1&action=sort&col='+col+'&dir='+dir);
				this.reload();
			}
		}
		,reload: function(){
			this.dirList = [];
			this.fileList = [];
			this.getDir(this.mode,1);
		}
		,message: function(v){
			var editor = getActiveEditor();
			if(editor)
				editor.windowManager.alert(v);
			else alert(v);
		}
		,showErrorMessage: function(v){
			this.message(v);
		}
		,confirm: function(message,callback){
			var editor = getActiveEditor();
			if(editor)
				editor.windowManager.confirm(message,callback);
			else callback(window.confirm(message));
		}
		,initDir: function(resp){
			if(typeof(resp) == 'object'){
				var $eDir = $(resp).find('dir')
					,$items = $eDir.find('>dir,>file')
					,comp = this;
				this.orderCol = $eDir.attr('orderCol');
				this.orderDir = $eDir.attr('orderDir');
				this.url = $eDir.attr('url');
				this.path = $eDir.attr('path');
				this.mode = $eDir.attr('mode');
				this.items = parseInt($eDir.attr('items'));
				this.pages = parseInt($eDir.attr('pages'));
				this.page = parseInt($eDir.attr('page'));
				$items.each(function(i){
					comp.appendItem(i,$(this));
				});
				comp.dirPath = [];
				$eDir.find('>path>item').each(function(i){
					comp.dirPath.push($(this).text());
				});
				
				this.checkState();
			}
		}
		,getDir: function(mode,page,path){
			var comp = this;
			comp.progressStart();
			$.ajax({
				type: 'GET'
				,url: '?xml=1&mode='+mode
					+(page ? '&page='+page : 1)
					+(path ? '&path='+encodeURIComponent(path) : '')
				,xhr: function(){
					var xmlHttpReq = $.ajaxSettings.xhr();
					xmlHttpReq.addEventListener('progress',function(evt){
						comp.request.percent = evt.lengthComputable ? evt.loaded / evt.total : 100;
					},false);
					return xmlHttpReq;
				}
			})
			.always(function(){
				comp.progressStop();
			})
			.done(function(resp){
				comp.initDir(resp);
				comp.initStickyHeaders();
			});
		}
		,appendItem: function(i,$e){
			if($e.get(0).tagName === 'dir'){
				this.dirList.push({
					id: $e.text() == '..' ? 'parentDir' : 'dir-'+this.page+'-'+i
					,name: $e.text()
					,selected: false
				});
			}else if($e.get(0).tagName === 'file'){
				this.fileList.push({
					id: 'file-'+this.page+'-'+i
					,name: $e.text()
					,ext: $e.attr('ext')
					,size: $e.attr('size')
					,date: $e.attr('date')
					,ico: $e.attr('ico')
					,img: $e.attr('img') === 'img'
					,imgThumbBg: 'url(\'thumb.php?w='+this.thumb.width+'&h='+this.thumb.height+'&src='+encodeURIComponent(this.path+$e.text())+'\')'
					,arch: $e.attr('arch') === 'arch'
					,selected: false
				});
			}
		}
		,getSelectedDirs: function(){
			var arItems = [];
			$(this.dirList).each(function(){
				if(parseInt(this.selected) == 1 && this.name != '..')
					arItems.push(this);
			});
			return arItems;
		}
		,getSelectedFiles: function(){
			var arItems = [];
			$(this.fileList).each(function(){
				if(parseInt(this.selected) == 1)
					arItems.push(this);
			});
			return arItems;
		}
		,checkState: function(){
			var noSelection = !this.getSelectedDirs().length && !this.getSelectedFiles().length;
			this.btn.remove.disabled =
			this.btn.rename.disabled =
			this.btn.download.disabled = noSelection;
		}
		,progressStart: function(){
			this.request.inProgress = true;
			this.request.percent = 10;
		}
		,progressStop: function(){
			this.request.inProgress = false;
			this.request.percent = 0;
		}
		,getSelectedPath: function(){
			var arFiles = this.getSelectedFiles();
			if(arFiles.length)
				return this.url+arFiles[0].name;
		}
		,changeMode: function(mode){
			this.mode = mode;
			$.get('?action=mode&mode='+mode);
			this.initStickyHeaders();
		}
		,initStickyHeaders: function(){
			$('table').stickyTableHeaders('destroy');
			$('table').stickyTableHeaders({scrollableArea: $('#output')});
		}
	}
	,mounted: function(){
		var comp = this
			,timeoutId = null;
		comp.getDir(this.mode,1);
		$('#fileupload').fileupload({
			dataType:'json'
			,limitMultiFileUploads:10
			,start:function(e){
				comp.progressStart();
			}
			,done:function(e,data){
				var errors = [];
				$(data.result.files).each(function(){
					if(this.error)
						errors.push(this.name+': '+this.error);
				});
				if(errors.length)
					comp.message(errors.join(', '));
				comp.progressStop();
				if(timeoutId)
					window.clearTimeout(timeoutId);
				timeoutId = window.setTimeout(function(){
					timeoutId = null;
					comp.reload();
				},500);
			}
			,fail:function(e,data){
				console.log('fail',data);
				comp.progressStop();
			}
			,progressall:function(e,data){
				comp.request.percent = parseInt(data.loaded/data.total*100,10);
			}
			,formData:[
				{name:'action',value:'upload'}
			]
		});
		if(window.frameElement)
			$('.wrapper').height($(window.frameElement).height() - $('.progress').outerHeight() - $('.toolbar').outerHeight());
	}
	,i18n: {
		messages: {
			default: {
				message: dirListMessages
			}
		}
	}
	
});


new Vue({
	el: '#app'
	,'i18n': new VueI18n({ locale: 'default' })
});