(function($) {
	$(function() {
		if($('select#ImportFolder')) {
			$('select#ImportFolder').change(function() {
				if($(this).val() == '')  {
					$('#import-holder').html('');
					$('#action_saveUploadForm').show();
				}
				else {
					$('#import-holder').load($(this).val(), {},function() {
						$('#action_saveUploadForm').hide();
						$('#Form_ImportForm li:not(.disabled)').click(function(e) {
							if(e.target.nodeName != "INPUT") {
								i = $(this).find('input'); 
								c = i.attr('checked'); 
								i.attr('checked', !c);
							}
							$(this).toggleClass('current');
							e.stopPropagation();
						});
						$('a[rel=all]').click(function() {
							$('#Form_ImportForm li:not(.disabled)').click();
							return false;
						});
						$('a[rel=none]').click(function() {
							$('#Form_ImportForm li input').attr('checked',false);
							return false;
						});
					});
				}				 
			}).find('option:contains((0 files))').attr('disabled',true);
		}
	});
	
	$().ajaxSend(function(r,s){  
	 $(".ajax-loader").fadeIn("fast");  
	});  
	   
	$().ajaxStop(function(r,s){  
	  $(".ajax-loader").fadeOut("fast");  
	});  
	
})(jQuery);
