<?php

namespace NyroDev\UtilityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class TinymceController extends AbstractController {
	
	public static function handleBrowserAction($container, Request $request, $file = null, $dirName = 'tinymce') {
		
		$fileManagerDir = dirname(dirname(__FILE__)).'/Resources/views/filemanager/';
		
		$path = $fileManagerDir.$file;
		
		if (strpos($file, '.php') !== false) {
			ob_start();
			
			if (isset($_GET['lang'])) {
				switch ($_GET['lang']) {
					case 'fr': $_GET['lang'] = 'fr_FR'; break;
					case 'en': $_GET['lang'] = 'en_EN'; break;
					case 'br': $_GET['lang'] = 'pt_BR'; break;
				}
			}
			set_include_path(get_include_path() . PATH_SEPARATOR . $fileManagerDir);
			
			//**********************
			//Path configuration
			//**********************
			// In this configuration the folder tree is
			// root
			//    |- source <- upload folder
			//    |- thumbs <- thumbnail folder [must have write permission (755)]
			//    |- filemanager
			//    |- js
			//    |   |- tinymce
			//    |   |   |- plugins
			//    |   |   |   |- responsivefilemanager
			//    |   |   |   |   |- plugin.min.js

			/*
			$base_url =
			   // Get HTTP/HTTPS
			   ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && !in_array(strtolower($_SERVER['HTTPS']),array('off','no'))) ? 'https' : 'http').
			   '://'.
			   // Get domain portion
			   $_SERVER['HTTP_HOST']; // DON'T TOUCH (base url (only domain) of site (without final /)).
			 */
			$base_url = $request->getScheme().'://'.$request->getHost().$request->getBasePath();
			
			$upload_dir = '/uploads/'.$dirName.'/'; // path from base_url to base of upload folder (with start and final /)
			
			$current_path = $container->getParameter('kernel.root_dir').'/../web/uploads/'.$dirName.'/'; // relative path from filemanager folder to upload folder (with final /)
			//thumbs folder can't put inside upload folder
			$thumbs_base_path = '../../../../../../../../web/uploads/'.$dirName.'Thumbs/'; // relative path from filemanager folder to thumbs folder (with final /)
			$thumbs_base_path_url = $base_url.'/uploads/'.$dirName.'Thumbs/';

			if (!file_exists($current_path))
				mkdir($current_path, 0777, true);
			if (!file_exists($fileManagerDir.$thumbs_base_path))
				mkdir($fileManagerDir.$thumbs_base_path, 0777, true);
			
			// OPTIONAL SECURITY
			// if set to true only those will access RF whose url contains the access key(akey) like: 
			// <input type="button" href="../filemanager/dialog.php?field_id=imgField&lang=en_EN&akey=myPrivateKey" value="Files">
			// in tinymce a new parameter added: filemanager_access_key:"myPrivateKey"
			// example tinymce config:
			// tiny init ...
			// 
			// external_filemanager_path:"../filemanager/",
			// filemanager_title:"Filemanager" ,
			// filemanager_access_key:"myPrivateKey" ,
			// ...
			define('USE_ACCESS_KEYS', FALSE); // TRUE or FALSE

			// add access keys eg: array('myPrivateKey', 'someoneElseKey');
			// keys should only containt (a-z A-Z 0-9 \ . _ -) characters
			// if you are integrating lets say to a cms for admins, i recommend making keys randomized something like this:
			// $username = 'Admin';
			// $salt = 'dsflFWR9u2xQa' (a hard coded string)
			// $akey = md5($username.$salt);
			// DO NOT use 'key' as access key!
			// Keys are CASE SENSITIVE!
			$access_keys = array('nyrodev/utility-bundle', $container->getParameter('secret'));

			//--------------------------------------------------------------------------------------------------------
			// YOU CAN COPY AND CHANGE THESE VARIABLES INTO FOLDERS config.php FILES TO CUSTOMIZE EACH FOLDER OPTIONS
			//--------------------------------------------------------------------------------------------------------

			$MaxSizeUpload = 100; //Mb

			// SERVER OVERRIDE
			if ((int)(ini_get('post_max_size')) < $MaxSizeUpload){
				$MaxSizeUpload = (int)(ini_get('post_max_size'));
			}

			$default_language 	= 'en_EN'; //default language file name
			$icon_theme 		= "ico"; //ico or ico_dark you can cusatomize just putting a folder inside filemanager/img
			$show_folder_size 	= TRUE; //Show or not show folder size in list view feature in filemanager (is possible, if there is a large folder, to greatly increase the calculations)
			$show_sorting_bar 	= TRUE; //Show or not show sorting feature in filemanager
			$loading_bar 		= TRUE; //Show or not show loading bar
			$transliteration 	= FALSE; //active or deactive the transliteration (mean convert all strange characters in A..Za..z0..9 characters)
			$convert_spaces  = FALSE; //convert all spaces on files name and folders name with _

			//*******************************************
			//Images limit and resizing configuration
			//*******************************************

			// set maximum pixel width and/or maximum pixel height for all images
			// If you set a maximum width or height, oversized images are converted to those limits. Images smaller than the limit(s) are unaffected
			// if you don't need a limit set both to 0
			$image_max_width  = 0;
			$image_max_height = 0;

			//Automatic resizing //
			// If you set $image_resizing to TRUE the script converts all uploaded images exactly to image_resizing_width x image_resizing_height dimension
			// If you set width or height to 0 the script automatically calculates the other dimension
			// Is possible that if you upload very big images the script not work to overcome this increase the php configuration of memory and time limit
			$image_resizing = FALSE;
			$image_resizing_width  = 0;
			$image_resizing_height = 0;

			//******************
			// Default layout setting
			//
			// 0 => boxes
			// 1 => detailed list (1 column)
			// 2 => columns list (multiple columns depending on the width of the page)
			// YOU CAN ALSO PASS THIS PARAMETERS USING SESSION VAR => $_SESSION['RF']["VIEW"]=
			//
			//******************
			$default_view = 0;

			//set if the filename is truncated when overflow first row 
			$ellipsis_title_after_first_row = TRUE;

			//*************************
			//Permissions configuration
			//******************
			$delete_files	 	  = TRUE;
			$create_folders	 	= $container->getParameter('nyroDev_utility.browser.allowAddDir');
			$delete_folders	 	= TRUE;
			$upload_files	 	  = TRUE;
			$rename_files	 	  = false;
			$rename_folders	 	= false;
			$duplicate_files 	= false;
			$copy_cut_files	 	= false; // for copy/cut files
			$copy_cut_dirs	 	= false; // for copy/cut directories
			$chmod_files 	 	  = FALSE; // change file permissions
			$chmod_dirs		 	  = FALSE; // change folder permissions
			$preview_text_files = TRUE; // eg.: txt, log etc.
			$edit_text_files 	  = TRUE; // eg.: txt, log etc.
			$create_text_files 	= false; // only create files with exts. defined in $editable_text_file_exts

			// you can preview these type of files if $preview_text_files is true
			$previewable_text_file_exts = array('txt', 'log', 'xml');

			// you can edit these type of files if $edit_text_files is true (only text based files)
			// you can create these type of files if $create_text_files is true (only text based files)
			// if you want you can add html,css etc. 
			// but for security reasons it's NOT RECOMMENDED!
			$editable_text_file_exts = array('txt', 'log', 'xml');

			// defines size limit for paste in MB / operation
			// set 'FALSE' for no limit
			$copy_cut_max_size	 = 100;
			// defines file count limit for paste / operation
			// set 'FALSE' for no limit
			$copy_cut_max_count	 = 200;
			//IF any of these limits reached, operation won't start and generate warning

			//**********************
			//Allowed extensions (lowercase insert)
			//**********************
			$ext_img = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'svg'); //Images
			$ext_file = array('doc', 'docx','rtf', 'pdf', 'xls', 'xlsx', 'txt', 'csv','html','xhtml','psd','sql','log','fla','xml','ade','adp','mdb','accdb','ppt','pptx','odt','ots','ott','odb','odg','otp','otg','odf','ods','odp','css','ai'); //Files
			$ext_video = array('mov', 'mpeg', 'm4v', 'mp4', 'avi', 'mpg','wma',"flv","webm"); //Video 
			$ext_music = array('mp3', 'm4a', 'ac3', 'aiff', 'mid','ogg','wav'); //Audio
			$ext_misc = array('zip', 'rar','gz','tar','iso','dmg'); //Archives

			$ext = array_merge($ext_img, $ext_file, $ext_misc, $ext_video,$ext_music); //allowed extensions

			/******************
			 * AVIARY config
			*******************/
			$aviary_active 	= false;
			$aviary_key 	= "dvh8qudbp6yx2bnp";
			$aviary_secret	= "m6xaym5q42rpw433";
			$aviary_version	= 3;
			$aviary_language= 'en';


			//The filter and sorter are managed through both javascript and php scripts because if you have a lot of
			//file in a folder the javascript script can't sort all or filter all, so the filemanager switch to php script.
			//The plugin automatic swich javascript to php when the current folder exceeds the below limit of files number
			$file_number_limit_js = 500;

			//**********************
			// Hidden files and folders
			//**********************
			// set the names of any folders you want hidden (eg "hidden_folder1", "hidden_folder2" ) Remember all folders with these names will be hidden (you can set any exceptions in config.php files on folders)
			$hidden_folders = array();
			// set the names of any files you want hidden. Remember these names will be hidden in all folders (eg "this_document.pdf", "that_image.jpg" )
			$hidden_files = array('config.php');

			/*******************
			 * JAVA upload 
			 *******************/
			$java_upload = false;
			$JAVAMaxSizeUpload = 200; //Gb


			//************************************
			//Thumbnail for external use creation
			//************************************


			// New image resized creation with fixed path from filemanager folder after uploading (thumbnails in fixed mode)
			// If you want create images resized out of upload folder for use with external script you can choose this method, 
			// You can create also more than one image at a time just simply add a value in the array
			// Remember than the image creation respect the folder hierarchy so if you are inside source/test/test1/ the new image will create at
			// path_from_filemanager/test/test1/
			// PS if there isn't write permission in your destination folder you must set it
			// 
			$fixed_image_creation                   = FALSE; //activate or not the creation of one or more image resized with fixed path from filemanager folder
			$fixed_path_from_filemanager            = array('../test/','../test1/'); //fixed path of the image folder from the current position on upload folder
			$fixed_image_creation_name_to_prepend   = array('','test_'); //name to prepend on filename
			$fixed_image_creation_to_append         = array('_test',''); //name to appendon filename
			$fixed_image_creation_width             = array(300,400); //width of image (you can leave empty if you set height)
			$fixed_image_creation_height            = array(200,''); //height of image (you can leave empty if you set width)
			/*
			  #             $option:     0 / exact = defined size;
			  #                          1 / portrait = keep aspect set height;
			  #                          2 / landscape = keep aspect set width;
			  #                          3 / auto = auto;
			  #                          4 / crop= resize and crop;
			 */
			$fixed_image_creation_option            = array('crop','auto'); //set the type of the crop


			// New image resized creation with relative path inside to upload folder after uploading (thumbnails in relative mode)
			// With Responsive filemanager you can create automatically resized image inside the upload folder, also more than one at a time
			// just simply add a value in the array
			// The image creation path is always relative so if i'm inside source/test/test1 and I upload an image, the path start from here
			// 
			$relative_image_creation                = FALSE; //activate or not the creation of one or more image resized with relative path from upload folder
			$relative_path_from_current_pos         = array('thumb/','thumb/'); //relative path of the image folder from the current position on upload folder
			$relative_image_creation_name_to_prepend= array('','test_'); //name to prepend on filename
			$relative_image_creation_name_to_append = array('_test',''); //name to append on filename
			$relative_image_creation_width          = array(300,400); //width of image (you can leave empty if you set height)
			$relative_image_creation_height         = array(200,''); //height of image (you can leave empty if you set width)
			/*
			  #             $option:     0 / exact = defined size;
			  #                          1 / portrait = keep aspect set height;
			  #                          2 / landscape = keep aspect set width;
			  #                          3 / auto = auto;
			  #                          4 / crop= resize and crop;
			 */
			$relative_image_creation_option         = array('crop','crop'); //set the type of the crop


			$nyrodev = $container->get('nyrodev');
			
			require($path);
			$content = ob_get_contents();
			ob_end_clean();
			
			$response = new \Symfony\Component\HttpFoundation\Response();
			$response->setContent($content);
		} else {
			$response = new \Symfony\Component\HttpFoundation\BinaryFileResponse($path);
			
			switch($response->getFile()->getExtension()) {
				case 'js': $response->headers->set('Content-Type', 'application/javascript'); break;
				case 'css': $response->headers->set('Content-Type', 'text/css'); break;
			}
			
			$response->setPublic();
			$response->setSharedMaxAge(3600);
			$response->setMaxAge(3600);
		}
		
		return $response;
	}
	
	public function browserAction(Request $request, $file = null, $dirName = 'tinymce') {
		return self::handleBrowserAction($this->container, $request, $file, $dirName);
		
		
		
		$allowAddDir = $this->container->getParameter('nyroDev_utility.browser.allowAddDir');
		$type = $this->getRequest()->query->get('type');
		$search = $this->getRequest()->query->get('search');
		$dir = $allowAddDir ? $this->getRequest()->query->get('dir') : null;
		$delete = $this->getRequest()->query->get('delete');
		$route = $this->getRequest()->get('_route');
		$routePrm = array(
			'type'=>$type,
			'dir'=>$dir
		);
		$uri = $this->generateUrl($route, $routePrm).'&';
		
		$webdir = 'uploads/tinymce/'.$type.'/';
		if ($allowAddDir && $dir)
			$webdir.= $dir.'/';
		$dirPath = $this->container->getParameter('kernel.root_dir').'/../web/'.$webdir;
		
		if (!is_dir($dirPath))
            mkdir($dirPath, 0777, true);
		
		if ($allowAddDir && $this->getRequest()->query->get('addDir')) {
			$addDir = $this->get('nyrodev')->urlify($this->getRequest()->query->get('addDir'));
			$addDirPath = $dirPath.$addDir.'/';
			if (!file_exists($addDirPath))
				mkdir($addDirPath, 0777, true);
			
			if (is_dir($addDirPath))
				$uri = $this->generateUrl($route, array_merge($routePrm, array('dir'=>($dir ? $dir.'/' : '').$addDir)));
			return $this->redirect($uri);
		}
		
		if ($allowAddDir && $this->getRequest()->query->get('deleteDir')) {
			$deleteDir = $this->get('nyrodev')->urlify($this->getRequest()->query->get('deleteDir'));
			$delDirPath = $dirPath.$deleteDir.'/';
			if (is_dir($delDirPath)) {
				$finder = new \Symfony\Component\Finder\Finder();
				$resources = $finder
							->files()
							->in($delDirPath)
							->name('*');
				foreach($resources as $r) {
					$path = $r.'';
					$this->get('nyrodev_image')->removeCache($path);
					unlink($path);
				}
				$fs = new \Symfony\Component\Filesystem\Filesystem();
				$fs->remove($delDirPath);
			}
			
			return $this->redirect($uri);
		}
		
		$form = $this->createFormBuilder()
					->add('file', 'file', array('label'=>$this->trans('nyrodev.browser.file')))
					->add('submit', 'submit', array('label'=>$this->trans('nyrodev.browser.send')))
					->getForm();
		
		$nyrodevService = $this->get('nyrodev');
		$assets = $this->get('templating.helper.assets');
		
		if ($delete) {
			$file = $dirPath.urldecode($delete);
			if (file_exists($file)) {
				$this->get('nyrodev_image')->removeCache($file);
				unlink($file);
				return $this->redirect($uri);
			}
		}
		
		$form->handleRequest($this->getRequest());
		if ($form->isValid()) {
			$file = $form->get('file')->getData();

			$nb = 1;
			$ext = $file->guessExtension();
			if ($ext)
				$ext = '.'.$ext;
			$name = $nyrodevService->getUniqFileName($dirPath, $file->getClientOriginalName());

			$file->move($dirPath, $name);
			unset($file);

			return $this->redirect($uri);
		}
		
		$dirs = array();
		$files = array();
		
		$pattern = '*';
		if ($search)
			$pattern.= mb_strtolower($search).'*';
		
		$version = '?'.$assets->getVersion();
		
		if ($allowAddDir) {
			$finderDir = new \Symfony\Component\Finder\Finder();
			$resources = $finderDir
						->depth(0)
						->directories()
						->sort(function(\SplFileInfo $a, \SplFileInfo $b) {
							return strnatcmp($a->getRealpath(), $b->getRealpath()) > 0;
						})
						->in($dirPath)
						->name($pattern);
			foreach($resources as $res) {
				$basename = $name = basename($res);
				$date = new \Datetime();
				$date->setTimestamp(filemtime($res));
				$dirs[] = array(
					$this->generateUrl($route, array_merge($routePrm, array('dir'=>($dir ? $dir.'/' : '').$name))),
					$name,
					$date,
					$uri.'deleteDir='.urlencode($basename).'&'
				);
			}
		}
		
		$finder = new \Symfony\Component\Finder\Finder();
		$resources = $finder
					->depth(0)
					->files()
					->sort(function(\SplFileInfo $a, \SplFileInfo $b) {
						return strnatcmp($a->getRealpath(), $b->getRealpath()) > 0;
					})
					->in($dirPath)
					->name($pattern);
		foreach($resources as $res) {
			if (is_file($res)) {
				$basename = $name = basename($res);
				if ($type == 'image' && strlen($name) > 15)
					$name = substr($name, 0, 15).'...'.pathinfo($res, PATHINFO_EXTENSION);
				$date = new \Datetime();
				$date->setTimestamp(filemtime($res));
				$size = $type == 'image' ? @getimagesize($res) : null;
				$files[] = array(
					str_replace($version, '', $assets->getUrl($webdir.$basename)),
					str_replace('\\', '/', $res.''),
					$name,
					$nyrodevService->humanFileSize($res),
					$date,
					$uri.'delete='.urlencode($basename).'&',
					$size
				);
			}
		}
		
		return $this->render('NyroDevUtilityBundle:Tinymce:browser.html.php', array(
			'uri'=>$uri,
			'route'=>$route,
			'routePrm'=>$routePrm,
			'type'=>$type,
			'dir'=>$dir,
			'search'=>$search,
			'dirs'=>$dirs,
			'files'=>$files,
			'form'=>$form->createView(),
			'allowAddDir'=>$allowAddDir
		));
	}

}