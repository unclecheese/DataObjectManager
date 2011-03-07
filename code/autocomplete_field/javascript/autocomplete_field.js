(function($) {
	var request = false;
	$.fn.autoComplete = function() {
		return this.each(function() {
			var $element = $(this);
			$element.initial_val = $element.val();
			$(this).attr('autocomplete','off')
				.focus(function() {
					$(this).toggleClass('focus');
					if($(this).val() == $element.initial_val)
						$(this).val('');
				})
				.keyup(function(e) {
					var $input = $(this);
					var $resultsDiv = $input.siblings('.autocomplete_results');
					url = $(this).metadata().url;        
		        	if ((e.keyCode == 9) || (e.keyCode == 13) || // tab, enter 
			           (e.keyCode == 16) || (e.keyCode == 17) || // shift, ctl 
			           (e.keyCode >= 18 && e.keyCode <= 20) || // alt, pause/break, caps lock
			           (e.keyCode == 27) || // esc 
			           (e.keyCode >= 33 && e.keyCode <= 35) || // page up, page down, end 
			           (e.keyCode >= 36 && e.keyCode <= 38) || // home, left, up 
			            (e.keyCode == 40) || // down 
			           (e.keyCode >= 36 && e.keyCode <= 40) || // home, left, up, right, down
			           (e.keyCode >= 44 && e.keyCode <= 45) || // print screen, insert 
			           (e.keyCode == 229) // Korean XP fires 2 keyup events, the key and 229 
		        	) return; 
						
					if(request) window.clearTimeout(request);
					request = window.setTimeout(function() {
						if($input.val().length) {
							$resultsDiv.load(
								url, 
								{q : $input.val()},
								function(data) {
									if(data.length)
										$resultsDiv.show();
									else
										$resultsDiv.hide();
								}
							);
						}
					},500)
				e.stopPropagation();
			})
			.blur(function() {
				$t = $(this);
				setTimeout(function() {
					$t.toggleClass('focus').val($element.initial_val).siblings('.autocomplete_results').hide();
				}, 500);
			})			
			
		});
	};
$(function() {
	$('input.autocomplete_input').livequery(function() {
		$(this).autoComplete();
	});
});
})(jQuery);