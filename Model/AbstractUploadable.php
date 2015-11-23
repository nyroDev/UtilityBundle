<?php

namespace NyroDev\UtilityBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractUploadable {
	
	const CONFIG_FIELD = 'field';
	const CONFIG_DIR = 'dir';
	const CONFIG_ROOTDIR = 'rootDir';
	
	const PATH_ORIGINAL = 'original';
	const PATH_ABSOLUTE = 'absolute';
	const PATH_WEB = 'web';
	
	/**
	 *
	 * @var \NyroDev\UtilityBundle\Services\MainService
	 */
	protected $service;
	
	public function setService(\NyroDev\UtilityBundle\Services\MainService $service) {
		$this->service = $service;
	}
	
	abstract protected function getFileFields();
	
	protected $temps = array();
	protected $directs = array();
	
	public function getFilePath($field, $place = AbstractUploadable::PATH_ORIGINAL) {
		$fieldFile = $this->getFileConfig($field, AbstractUploadable::CONFIG_FIELD);
		$accessor = PropertyAccess::createPropertyAccessor();
		
		$original = $accessor->getValue($this, $fieldFile);
		
		if (!$original)
			return null;
		
		switch($place) {
			case AbstractUploadable::PATH_ABSOLUTE:
				return $this->getFileConfig($field, AbstractUploadable::CONFIG_ROOTDIR).'/'.$original;
			case AbstractUploadable::PATH_WEB:
				return $this->getFileConfig($field, AbstractUploadable::CONFIG_DIR).'/'.$original;
			case AbstractUploadable::PATH_ORIGINAL:
			default:
				return $original;
		}
	}
	
	public function getWebPath($field) {
		return $this->getFilePath($field, AbstractUploadable::PATH_WEB);
	}
	
	public function getAbsolutePath($field) {
		return $this->getFilePath($field, AbstractUploadable::PATH_ABSOLUTE);
	}
	
	public function setFilePath($field, $value) {
		$accessor = PropertyAccess::createPropertyAccessor();
		$accessor->setValue($this, $this->getFileConfig($field, AbstractUploadable::CONFIG_FIELD), $value);
	}
	
	public function removeFile($field) {
		$this->removeFileReal($this->getFilePath($field, AbstractUploadable::PATH_ABSOLUTE));
		$this->setFilePath($field, null);
	}

	public function getFileConfig($field, $config) {
		$fileFields = $this->getFileFields();
		if (isset($fileFields[$field]) && isset($fileFields[$field][$config]))
			return $fileFields[$field][$config];
		switch($config) {
			case AbstractUploadable::CONFIG_FIELD:
				return $field.'File';
			case AbstractUploadable::CONFIG_DIR:
				return 'uploads/'.$field;
			case AbstractUploadable::CONFIG_ROOTDIR:
				return __DIR__.'/../../../../../../web/'.$this->getFileConfig($field, AbstractUploadable::CONFIG_DIR);
		}
		return null;
	}
	
	public function setDirectFile($field, $source, $filename = null, $sourceIsContent = false) {
		$this->directs[$field] = array(
			'source'=>$source,
			'sourceIsContent'=>$sourceIsContent,
			'filename'=>$filename ? $filename : basename($source)
		);
		$original = $this->getFilePath($field);
		if ($original) {
			$this->temps[$field] = $original;
			$value = null;
		} else {
			$value = 'initial';
		}

		$this->setFilePath($field, $value);
	}
	
	protected function setUploadFile($field, UploadedFile $file = null) {
		$this->$field = $file;

		$original = $this->getFilePath($field);
		if ($original) {
			$this->temps[$field] = $original;
			$value = null;
		} else {
			$value = 'initial';
		}

		$this->setFilePath($field, $value);
	}
	
	public function preUpload() {
		foreach($this->getFileFields() as $field=>$config) {
			$file = isset($this->directs[$field]) ? $this->directs[$field] : $this->$field;
			if (!is_null($file)) {
				$value = null;
				if ($file instanceof UploadedFile) {
					if ($this->service) {
						$value = $this->service->getUniqFileName($this->getFileConfig($field, AbstractUploadable::CONFIG_ROOTDIR), $file->getClientOriginalName());
					} else {
						$filename = sha1(uniqid(mt_rand(), true));
						$value = $filename.'.'.$file->guessExtension();
					}
				} else {
					$value = $this->service->getUniqFileName($this->getFileConfig($field, AbstractUploadable::CONFIG_ROOTDIR), $file['filename']);
				}
				
				$this->setFilePath($field, $value);
			}
		}
	}

	public function upload() {
		foreach($this->getFileFields() as $field=>$config) {
			$file = isset($this->directs[$field]) ? $this->directs[$field] : $this->$field;
			if (!is_null($file)) {
				$rootDir = $this->getFileConfig($field, AbstractUploadable::CONFIG_ROOTDIR);
				if ($file instanceof UploadedFile) {
					$file->move($rootDir, $this->getFilePath($field));
				} else {
					$fs = new \Symfony\Component\Filesystem\Filesystem();
					if (!$fs->exists($rootDir))
						$fs->mkdir($rootDir);
					file_put_contents($rootDir.'/'.$this->getFilePath($field), $file['sourceIsContent'] ? $file['source'] : file_get_contents($file['source']));
				}

				// check if we have an old image
				if (isset($this->temps[$field])) {
					// delete the old image
					$this->removeFileReal($rootDir.'/'.$this->temps[$field]);
					// clear the temp image path
					unset($this->temps[$field]);
				}
			}
			$this->$field = null;
		}
		$this->service = null;
	}

	public function removeUpload() {
		foreach($this->getFileFields() as $field=>$config)
			$this->removeFileReal($this->getFilePath($field, AbstractUploadable::PATH_ABSOLUTE));
	}
	
	protected function removeFileReal($file) {
		if ($file && file_exists($file)) {
			unlink($file);
			if ($this->service)
				$this->service->get('nyrodev_image')->removeCache($file);
		}
	}
}