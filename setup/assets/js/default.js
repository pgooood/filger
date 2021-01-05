function posContentBlock(){
	var	$e = $('#content-block')
		,wh = $(window).height()
		,eh = $e.outerHeight();
	$e.css('margin-top',wh > eh ? (wh - eh) / 2 : 10);
};
function setFullHeight() {
	$('.set-full-height').css('height', $(window).height());
	posContentBlock();
};
/* tabs */
function checkTabFields(a){
	var err;
	$($(a).attr('href'))
		.find('[required]').each(function(){
			var $tab = $(this);
			if($tab.val().length)
				$tab.closest('.form-group').removeClass('has-error');
			else{
				$tab.closest('.form-group').addClass('has-error');
				err = true;
			};
		})
		.find('[type=number]').each(function(){
			var v = $(this).val();
			if(v.length && isNaN(v))
				err = true;
		});
	return err;
}
function nextTab(e){
	var id = $(e).closest('.tab-pane').attr('id')
		,$tab = $('a[href="#'+id+'"]')
		,err = checkTabFields($tab);
	if(!err)
		$tab.closest('li').next('li').find('a').tab('show');
};
function prevTab(e){
	var id = $(e).closest('.tab-pane').attr('id');
	$('a[href="#'+id+'"]').closest('li').prev('li').find('a').tab('show');
};

$(function(){
	//jquery file upload installer
	var $alert = $('#jqueryFileUploadInstaller .alert');
	function loader(status){
		var $progress = $('#jqueryFileUploadInstaller .progress')
			,$progressBar = $('#jqueryFileUploadInstaller .progress-bar');
		if(status){
			$progress.show();
			$progressBar.width('100%');
		}else{
			$progress.hide();
			$progressBar.width('0%');
		}
	};
	function responseAlert(data){
		if(data){
			$alert.removeClass('alert-success alert-info alert-warning alert-danger');
			$alert.addClass('alert-'+data.status);
			$alert.html(data.html);
			$alert.show();
		}else
			$alert.hide();
	};
	$alert.hide();
	loader(true);
	$.get('?action=ajax_fileupload_status')
		.done(function(data){
			if(data.html){
				responseAlert(data);
				$alert.find('a.install-link').click(function(){
					installFileUpload($(this).attr('href'));
					return false;
				});
			};
		}).fail(function(){
			responseAlert({
				status: 'danger'
				,html: 'Error: request failed'
			});
		}).always(function(){
			loader(false);
		});
	function installFileUpload(url){
		loader(true);
		$.get(url).done(function(data){
				responseAlert(data);
			}).fail(function(){
				responseAlert({
					status: 'danger'
					,html: 'Error: request failed'
				});
			}).always(function(){
				loader(false);
			});
	};
});

$(function(){
	$(window).resize(setFullHeight);
	setFullHeight();
	
	//tabs
	$('#setup-tabs a').on('show.bs.tab',function(e){
		var err;
		$(e.relatedTarget)
			.find('[required]').each(function(){
				var $tab = $(this);
				if($tab.val().length)
					$tab.closest('.form-group').removeClass('has-error');
				else{
					$tab.closest('.form-group').addClass('has-error');
					err = true;
				};
			})
			.find('[type=number]').each(function(){
				var v = $(this).val();
				if(v.length && isNaN(v))
					err = true;
			});
		return !err;
	});
	$('#setup-tabs a').click(function(e){
		return false;
		//$(this).tab('show');
	});
});