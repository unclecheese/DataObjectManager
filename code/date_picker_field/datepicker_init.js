var date_picker_format;
(function($) {
  $(function() {
	$('.datepicker input').livequery(function() {
		date_picker_format = $(this).parents('div.datepicker:first').metadata().dateFormat;
		$(this).datepicker({dateFormat : date_picker_format, buttonImage : '/sapphire/images/calendar-icon.gif', buttonImageOnly : true});
	});
	
	$('a[rel=clear-btn]').live("click",function() {
		$(this).prev('input').val('');			
	});    
  });
})(jQuery)
