jQuery(function($) {
	var tinymceLoaded = false,
		tinymceLoading = false,
		tinymceLoadingQueue = [],
		tinymceLoad = function(url, clb) {
			if (tinymceLoading) {
				tinymceLoadingQueue.push(clb);
			} else if (!tinymceLoaded) {
				tinymceLoading = true;
				window.tinyMCEPreInit = {
					'base': url.substr(0, url.lastIndexOf('/')),
					'suffix': '.min'
				};
				$.ajax({
					url: url,
					cache: true,
					dataType: 'script'
				}).done(function() {
					tinymceLoading = false;
					tinymceLoaded = true;
					clb();
					if (tinymceLoadingQueue.length) {
						$.each(tinymceLoadingQueue, function(k, v) {
							v();
						});
					}
				});
			} else {
				clb();
			}
		},
		tinymceKey = 'tinymce_',
		tinymceKeyLn = tinymceKey.length;
	
	$.fn.extend({
		myTinymceDataSearch: function() {
			var opts = {};
			$.each($(this).first().data(), function(i, e) {
				if (i.indexOf(tinymceKey) == 0) {
					if (typeof e == 'string' && e.indexOf('function(') == 0) {
						eval('window.tinyval = '+e+';');
						e = window.tinyval;
						delete(window.tinyval);
					}
					opts[i.substring(tinymceKeyLn)] = e;
				}
			});
			return opts;
		},
		myTinymce: function(options, tinymceurl) {
			return this.each(function() {
				var me = $(this),
					opts = $.extend({
						oninit: function(ed) {
							me.trigger('tinmceInit', [ed]);
						}
					}, options);
				if (!tinymceurl)
					$.extend(opts, me.myTinymceDataSearch());
				tinymceLoad(tinymceurl ? tinymceurl : me.data('tinymceurl'), function() {
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
		}
	});
	
	$('textarea.tinymce').myTinymce();
});