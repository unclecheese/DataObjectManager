(function($) {
  $(function() {
    var ie = $.browser.msie;
    $('body').removeClass('loading');
		$('iframe').css({'width':'433px'});		

		var iframe_height = window.parent.jQuery('#facebox iframe').height(); // - 82;
    var diff = $('body').height() - $('#field-holder').height();
    var fields_height = (iframe_height - diff)-((ie) ? 30 : 50);
		var top = fields_height + diff-((ie) ? 55 : 21);
				
    $('#field-holder').css({'height' : fields_height + 'px'});
    $('#fade').css({
    	'top' : top + 'px',
    	'width' : ($('#field-holder').width() - ((ie) ? 0 : 10)) + 'px' ,
    	'left' : ((ie) ? 10 : 0) + 'px'
    });

		if($('#duplicate-form')) {
			$('#duplicate-form').hide();
			$('#duplicate-link').click(function() {
				$('#duplicate-form').slideToggle();
				return false;
			});
			$('#duplicate-form form').submit(function() {
				if(isNaN($('#copies').val()))
					alert('Number of copies must be an integer.');
				else {
					$t = $(this);
					$.post(
						$t.attr('action'),
						{
							'Count' : $('#copies').val(),
							'Relations' : $('#relations').attr('checked') ? "1" : "0"
						},
						function(data) {
							$('#message').html(data).show();
						}					
					);
				}
				return false;
			});
		}

  });
})(jQuery);