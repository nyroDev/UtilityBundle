<?php

namespace NyroDev\UtilityBundle\Controller;

class TinymceController extends AbstractController {
	
	public function browserAction() {
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