$(function() {
	var tinymceLoaded = false,
		tinymceLoad = function(url, clb) {
			if (!tinymceLoaded) {
				window.tinyMCEPreInit = {
					'base': url.substr(0, url.lastIndexOf('/')),
					'suffix': '.min'
				};
				$.ajax({
					url: url,
					cache: true,
					dataType: 'script'
				}).done(function() {
					tinymceLoaded = true;
					clb();
				});
			} else {
				clb();
			}
		},
		tinymceKey = 'tinymce_',
		tinymceKeyLn = tinymceKey.length;
	
	$('textarea.tinymce').each(function() {
		var me = $(this),
			opts = {};
		$.each(me.data(), function(i, e) {
			if (i.indexOf(tinymceKey) == 0) {
				if (typeof e == 'string' && e.indexOf('function(') == 0) {
					eval('window.tinyval = '+e+';');
					e = window.tinyval;
					delete(window.tinyval);
					window.tinyval = null;
				}
				opts[i.substring(tinymceKeyLn)] = e;
			}
		});
		tinymceLoad(me.data('tinymceurl'), function() {
			if (me.data('browser_url')) {
				opts['file_browser_callback'] = function(field_name, url, type, win) {
					parent.nyroBrowserField = field_name;
					parent.nyroBrowserWinBrowse = tinyMCE.activeEditor.windowManager.open({
						url: me.data('browser_url')+'?type='+type+'&',
						title: me.data('browser_title'),
						width: me.data('browser_width'),
						height: me.data('browser_height'),
						resizable: true,
						maximizable: true,
						scrollbars: true
					});
					return false;
				};
			}
			if (me.is('[required]'))
				me.removeProp('required').removeAttr('required');
			me.tinymce(opts);
		});
	});
});