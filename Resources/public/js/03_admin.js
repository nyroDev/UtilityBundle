jQuery(function($) {
	var currentFileDelete = $('.currentFileDelete');
	
	if (currentFileDelete.length) {
		currentFileDelete.on('click', function(e) {
			e.preventDefault();
			var me = $(this);
			if (confirm(me.data('confirm'))) {
				var row = me.closest('.form_row');
				row
					.append('<input type="hidden" name="'+me.data('name')+'" value="1" />')
					.find('.currentFile').remove()
				me.remove();
			}
		});
	}

});