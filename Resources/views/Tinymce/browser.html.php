<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo $view['translator']->trans('nyrodev.browser.title') ?></title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="title" content="<?php echo $view['translator']->trans('nyrodev.browser.title') ?>" />

	<?php foreach ($view['assetic']->stylesheets(
        array('@NyroDevUtilityBundle/Resources/public/css/nyrodevBrowser.css'),
        array('?yui_css'),
        array('output' => 'css/nyrodevBrowser.css')) as $url): ?>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $view->escape($url) ?>" />
	<?php endforeach; ?>
</head>
<body>
	<?php echo $view['form']->form($form) ?>
	<hr />
	<fieldset id="files" class="<?php echo $type ?>">
		<legend><?php echo $view['translator']->trans('nyrodev.browser.files') ?></legend>
		<form class="formulaires" action="<?php echo $uri ?>" method="get">
			<input type="hidden" name="type" value="<?php echo $type ?>" />
			<input type="hidden" name="dir" value="<?php echo $dir ?>" />
			<input type="text" name="search" value="<?php echo $search ?>" size="25" placeholder="<?php echo $view['translator']->trans('nyrodev.browser.search') ?>" />
			<input type="submit" value="<?php echo $view['translator']->trans('nyrodev.browser.search') ?>" />
		</form>
		<br />
		<?php if ($allowAddDir): ?>
			<form class="formulaires" action="<?php echo $uri ?>" method="get">
				<input type="hidden" name="type" value="<?php echo $type ?>" />
				<input type="hidden" name="dir" value="<?php echo $dir ?>" />
				<input type="text" name="addDir" value="" size="25" placeholder="<?php echo $view['translator']->trans('nyrodev.browser.directory') ?>" />
				<input type="submit" value="<?php echo $view['translator']->trans('nyrodev.browser.addDir') ?>" />
			</form>
		<?php endif; ?>
		<?php
        if ($dir) {
            $tmp = explode('/', $dir);
            $curDir = '';
            echo '<a href="'.$view['router']->generate($route, array_merge($routePrm, array('dir' => $curDir))).'">'.$view['translator']->trans('nyrodev.browser.rootDir').'</a>';
            foreach ($tmp as $d) {
                $curDir .= ($curDir ? '/' : '').$d;
                echo ' / <a href="'.$view['router']->generate($route, array_merge($routePrm, array('dir' => $curDir))).'">'.$d.'</a>';
            }
        }
        ?>
		<?php if (count($files) || count($dirs)): ?>
			<?php if ($type == 'image'): ?>
			<ul>
				<?php foreach ($dirs as $d): ?>
					<li>
						<a href="<?php echo $d[0] ?>" title="<?php echo strftime($view['translator']->trans('date.short'), $d[2]->getTimestamp()) ?>" class="dir">
							<?php echo $d[1] ?>
						</a>
						<a href="<?php echo $d[3] ?>" class="delete"><?php echo $view['translator']->trans('nyrodev.browser.delete') ?></a>
					</li>
				<?php endforeach; ?>
				<?php foreach ($files as $f): ?>
				<li>
					<a href="<?php echo $f[0] ?>" title="<?php echo $f[3].', '.strftime($view['translator']->trans('date.short'), $f[4]->getTimestamp()) ?>" <?php
                        if (isset($f[6]) && is_array($f[6])) {
                            echo 'data-width="'.$f[6][0].'" data-height="'.$f[6][1].'"';
                        }
                        ?> class="fileLink">
						<img src="<?php echo $view['nyrodev_image']->resize($f[1], 'browser') ?>" alt="image" /><br />
						<?php echo $f[2] ?>
					</a>
					<a href="<?php echo $f[5] ?>" class="delete"><?php echo $view['translator']->trans('nyrodev.browser.delete') ?></a>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php else: ?>
			<table>
				<tr>
					<th><?php echo $view['translator']->trans('nyrodev.browser.name') ?></th>
					<th class="size"><?php echo $view['translator']->trans('nyrodev.browser.size') ?></th>
					<th class="date"><?php echo $view['translator']->trans('nyrodev.browser.date') ?></th>
				</tr>
				<?php foreach ($files as $f): ?>
				<tr>
					<td>
						<a href="<?php echo $f[0] ?>" title="<?php echo $f[3].', '.strftime($view['translator']->trans('date.short'), $f[4]->getTimestamp()) ?>" class="fileLink"><?php echo $f[2] ?></a>
						<a href="<?php echo $f[5] ?>" class="delete"><?php echo $view['translator']->trans('nyrodev.browser.delete') ?></a>
					</td>
					<td class="size"><?php echo $f[3] ?></td>
					<td class="date"><?php echo strftime($view['translator']->trans('date.short'), $f[4]->getTimestamp()) ?></td>
				</tr>
				<?php endforeach; ?>
			</table>
			<?php endif; ?>
		<?php else: ?>
			<p><?php echo $view['translator']->trans('nyrodev.browser.noFiles') ?></p>
		<?php endif; ?>
	</fieldset>
	<?php foreach ($view['assetic']->javascripts(
        array('@NyroDevUtilityBundle/Resources/public/js/*jquery.js'),
        array('?yui_js'),
        array('output' => 'js/jquery.js')) as $url): ?>
		<script src="<?php echo $view->escape($url) ?>" type="text/javascript"></script>
	<?php endforeach; ?>
	<script type="text/javascript">
	//<![CDATA[
	$(function() {
		$('#files').delegate('.fileLink', 'click', function(e) {
			e.preventDefault();

			var me = $(this),
				url = me.attr('href'),
				file = parent.$('#'+parent.nyroBrowserField),
				panel = file.closest('.mce-window'),
				labelX = panel.find('label:contains("x")');

			file.val(url);

			if (labelX.length) {
				labelX.each(function() {
					var lbl = $(this);
					if ($.trim(lbl.text()) == 'x') {
						lbl.prev('input').val(me.data('width'));
						lbl.next('input').val(me.data('height'));
					}
				})
			}

			parent.nyroBrowserWinBrowse.close();
		});
	});

	//]]>
	</script>
</body>
</html>