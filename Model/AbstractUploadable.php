<?php

namespace NyroDev\UtilityBundle\Model;

use NyroDev\UtilityBundle\Services\ImageService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractUploadable
{
    const CONFIG_FIELD = 'field';
    const CONFIG_DIR = 'dir';
    const CONFIG_ROOTDIR = 'rootDir';

    const PATH_ORIGINAL = 'original';
    const PATH_ABSOLUTE = 'absolute';
    const PATH_WEB = 'web';

    const FILEPATH_REMOVE = 'remove';
    const FILEPATH_DIRECT = 'direct';
    const FILEPATH_UPLOAD = 'upload';
    const FILEPATH_PREUPLOAD = 'preupload';

    /**
     * @var \NyroDev\UtilityBundle\Services\MainService
     */
    protected $service;

    public function setService(\NyroDev\UtilityBundle\Services\MainService $service)
    {
        $this->service = $service;
    }

    abstract protected function getFileFields();

    protected $temps = [];
    protected $directs = [];

    public function getFilePath($field, $place = self::PATH_ORIGINAL)
    {
        $fieldFile = $this->getFileConfig($field, self::CONFIG_FIELD);
        $accessor = PropertyAccess::createPropertyAccessor();

        $original = $accessor->getValue($this, $fieldFile);

        if (!$original) {
            return;
        }

        switch ($place) {
            case self::PATH_ABSOLUTE:
                return $this->getFileConfig($field, self::CONFIG_ROOTDIR).'/'.$original;
            case self::PATH_WEB:
                return $this->getFileConfig($field, self::CONFIG_DIR).'/'.$original;
            case self::PATH_ORIGINAL:
            default:
                return $original;
        }
    }

    public function getWebPath($field)
    {
        return $this->getFilePath($field, self::PATH_WEB);
    }

    public function getAbsolutePath($field)
    {
        return $this->getFilePath($field, self::PATH_ABSOLUTE);
    }

    public function setFilePath($field, $value, $step)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($this, $this->getFileConfig($field, self::CONFIG_FIELD), $value);
    }

    public function removeFile($field)
    {
        $this->removeFileReal($this->getFilePath($field, self::PATH_ABSOLUTE));
        $this->setFilePath($field, null, self::FILEPATH_REMOVE);
    }

    public function getFileConfig($field, $config)
    {
        $fileFields = $this->getFileFields();
        if (isset($fileFields[$field]) && isset($fileFields[$field][$config])) {
            return $fileFields[$field][$config];
        }
        switch ($config) {
            case self::CONFIG_FIELD:
                return $field.'File';
            case self::CONFIG_DIR:
                return 'uploads/'.$field;
            case self::CONFIG_ROOTDIR:
                return __DIR__.'/../../../../public/'.$this->getFileConfig($field, self::CONFIG_DIR);
        }

        return;
    }

    public function setDirectFile($field, $source, $filename = null, $sourceIsContent = false)
    {
        $this->directs[$field] = [
            'source' => $source,
            'sourceIsContent' => $sourceIsContent,
            'filename' => $filename ? $filename : basename($source),
        ];
        $original = $this->getFilePath($field);
        if ($original) {
            $this->temps[$field] = $original;
            $value = null;
        } else {
            $value = 'initial';
        }

        $this->setFilePath($field, $value, self::FILEPATH_DIRECT);
    }

    protected function setUploadFile($field, UploadedFile $file = null)
    {
        $this->$field = $file;

        $original = $this->getFilePath($field);
        if ($original) {
            $this->temps[$field] = $original;
            $value = null;
        } else {
            $value = 'initial';
        }

        $this->setFilePath($field, $value, self::FILEPATH_UPLOAD);
    }

    public function preUpload()
    {
        foreach ($this->getFileFields() as $field => $config) {
            $file = isset($this->directs[$field]) ? $this->directs[$field] : $this->$field;
            if (!is_null($file)) {
                $value = null;
                if ($file instanceof UploadedFile) {
                    $value = $this->getNewFilename($field, $file->getClientOriginalName(), $file->guessExtension());
                } else {
                    $value = $this->getNewFilename($field, $file['filename']);
                }
                $this->setFilePath($field, $value, self::FILEPATH_PREUPLOAD);
            }
        }
    }

    protected function getNewFilename($field, $originalName, $extension = null)
    {
        return $this->getNewFilenameInDir($this->getFileConfig($field, self::CONFIG_ROOTDIR), $originalName, $extension);
    }

    protected function getNewFilenameInDir($dir, $originalName, $extension = null)
    {
        $value = $originalName;
        if ($this->service) {
            $value = $this->service->getUniqFileName($dir, $originalName);
        } else {
            $fs = new Filesystem();

            if (!$extension) {
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            }

            if ($extension) {
                $extension = '.'.$extension;
            }

            $filename = sha1(uniqid(mt_rand(), true));
            while ($fs->exists($dir.'/'.$filename.$extension)) {
                $filename = sha1(uniqid(mt_rand(), true));
            }

            $value = $filename.$extension;
        }

        return $value;
    }

    public function upload()
    {
        foreach ($this->getFileFields() as $field => $config) {
            $file = isset($this->directs[$field]) ? $this->directs[$field] : $this->$field;
            if (!is_null($file)) {
                $rootDir = $this->getFileConfig($field, self::CONFIG_ROOTDIR);
                $fullPath = $rootDir.'/'.$this->getFilePath($field);

                $fs = new Filesystem();
                if (!$fs->exists($rootDir)) {
                    $fs->mkdir($rootDir);
                }

                if ($file instanceof UploadedFile) {
                    if ($file->isValid()) {
                        $file->move(dirname($fullPath), basename($fullPath));
                    } elseif (!is_uploaded_file($file->getPathname())) {
                        $fs->mkdir(dirname($fullPath));
                        try {
                            $fs->rename($file->getPathname(), $fullPath);
                        } catch (\Exception $e) {
                            // IN NFS rename, it could fix some trouble doing a catch here
                            if (!$fs->exists($fullPath)) {
                                throw $e;
                            }
                        }
                    }
                } else {
                    if ($file['sourceIsContent']) {
                        $fs->dumpFile($fullPath, $file['source']);
                    } else {
                        $fs->copy($file['source'], $fullPath);
                    }
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

    public function removeUpload()
    {
        foreach ($this->getFileFields() as $field => $config) {
            $this->removeFileReal($this->getFilePath($field, self::PATH_ABSOLUTE));
        }
    }

    protected function removeFileReal($file)
    {
        $fs = new Filesystem();
        if ($file && $fs->exists($file)) {
            $fs->remove($file);
            if ($this->service) {
                $this->service->get(ImageService::class)->removeCache($file);
            }
        }
    }
}
