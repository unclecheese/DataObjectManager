(function($) {
$(function() {
	$('.filtereddropdownset select').live("change", function() {
		var $t = $(this);
		var $target = $t.closest('.fieldgroupField').next('.fieldgroupField').find('select');
		if(!$target.length) return;

		
		$.ajax({
			url: $t.metadata().updateLink,
			dataType: 'json',
			data: {q : $t.val()},
			success: function(data) {
				var $target = $t.closest('.fieldgroupField').next('.fieldgroupField').find('select');
				var currentValue = $target.val();
				$target.html('');
				if($target.metadata().emptyString) {
					$target.append($('<option value="">'+$target.metadata().emptyString+'</option>'));
				}
				for(i in data) {
					$target.append($('<option value="'+i+'">'+data[i]+'</option>'));
				}
				$target.val(currentValue);
			}
		});
	});
	$('.filtereddropdownset select').change();
});
})(jQuery);