(function($) {
	$(function(){
		$('a[rel=clear-btn]').click(function(){
			$(this).prev('input').val('');	
		});
	});
})(jQuery);