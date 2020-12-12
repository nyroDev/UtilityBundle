jQuery(function ($) {
	var tinymceLoaded = false,
		tinymceLoading = false,
		tinymceLoadingQueue = [],
		searchFuncOrRegexp = function (data) {
			if (typeof data == 'string') {
				if (data.indexOf('function(') === 0) {
					eval('window.tinyval = ' + data + ';');
					data = window.tinyval;
					delete(window.tinyval);
				} else if (data.indexOf('reg/') === 0 && data.lastIndexOf('/') === data.length - 1) {
					data = new RegExp(data.substring(4, data.length - 1));
				}
			} else if (typeof data == 'object') {
				var tmp = Object.prototype.toString.call(data) == '[object Array]' ? [] : {};
				$.each(data, function (k, v) {
					tmp[k] = searchFuncOrRegexp(v);
				});
				data = tmp;
			}
			return data;
		},
		tinymceKey = 'tinymce_';

	$.extend({
		tinymceLoad: function (url, clb) {
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
				}).done(function () {
					tinymceLoading = false;
					tinymceLoaded = true;
					clb();
					if (tinymceLoadingQueue.length) {
						$.each(tinymceLoadingQueue, function (k, v) {
							v();
						});
					}
				});
			} else {
				clb();
			}
		},
		tinymceLoaded: function (clb) {
			if (!tinymceLoaded) {
				tinymceLoadingQueue.push(clb);
			} else {
				clb();
			}
		}
	});

	$.fn.extend({
		myTinymceDataSearch: function (tKey) {
			tKey = tKey || tinymceKey;
			var opts = {},
				tKeyLn = tKey.length;
			$.each($(this).first().data(), function (i, e) {
				if (i.indexOf(tKey) == 0) {
					opts[i.substring(tKeyLn)] = searchFuncOrRegexp(e);
				}
			});
			return opts;
		},
		myTinymce: function (options, tinymceurl) {
			return this.each(function () {
				var me = $(this),
					opts = $.extend({
						oninit: function (ed) {
							me.trigger('tinmceInit', [ed]);
						}
					}, options);
				if (!tinymceurl) {
					$.extend(opts, me.myTinymceDataSearch());
				}
				$.tinymceLoad(tinymceurl ? tinymceurl : me.data('tinymceurl'), function () {
					if (me.is('[required]')) {
						me.removeProp('required').removeAttr('required');
					}
					me.tinymce(opts);
				});
			});
		}
	});

	$('textarea.tinymce').myTinymce();
});