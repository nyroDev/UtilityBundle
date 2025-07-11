<?php

namespace NyroDev\UtilityBundle\Controller;

use NyroDev\UtilityBundle\Services\ImageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\Process\Process;

class AssetsController extends AbstractController
{
    public function __construct(
        private readonly ImageService $imageService,
        private readonly string $projectDir,
    ) {
    }

    public function resize($dims, $path): BinaryFileResponse
    {
        $publicPath = $this->projectDir.'/public/';
        $fs = new Filesystem();
        $srcPath = $publicPath.$path;

        if (!$fs->exists($srcPath)) {
            throw $this->createNotFoundException();
        }

        $cachePath = $this->imageService->getCachePath($srcPath);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (in_array($extension, ['tif', 'tiff'])) {
            $extension = 'png';
        } elseif (!in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
            $extension = 'jpg';
        }

        $dstPath = "{$cachePath}resize_{$dims}.{$extension}";

        if (!$fs->exists($dstPath)) {
            $dstDir = dirname($dstPath);

            if (!$fs->exists($dstDir)) {
                $fs->mkdir($dstDir);
            }

            $dimsA = explode('x', $dims);
            $width = $dimsA[0];
            $height = $dimsA[1] ?? '-';

            $crop = false;

            if ('-' != $height && isset($dimsA[2]) && 'fit' === $dimsA[2]) {
                $imageSize = getimagesize($srcPath);

                $ratioRequested = $width / $height;
                if ($width > $imageSize[0]) {
                    $width = $imageSize[0];
                    $height = round($width / $ratioRequested);
                }
                if ($height > $imageSize[1]) {
                    $height = $imageSize[1];
                    $width = round($height * $ratioRequested);
                }

                $ratioW = $imageSize[0] / $width;
                $ratioH = $imageSize[1] / $height;

                if ($ratioW > $ratioH) {
                    $resizedWidth = $imageSize[0] / $ratioH;
                    $crop = $width.'x'.$height.'+'.round(($resizedWidth - $width) / 2).'+0';
                    $width = '-';
                } elseif ($ratioW < $ratioH) {
                    $resizedHeight = $imageSize[1] / $ratioW;
                    $crop = $width.'x'.$height.'+0+'.round(($resizedHeight - $height) / 2);
                    $height = '-';
                }
            }

            $resize = '-' == $width || '-' == $height;

            if ('-' === $width) {
                $width = '';
            }
            if ('-' === $height) {
                $height = '';
            }

            $cmd = [
                'convert',
                $srcPath.'[0]',
                '-background', 'none',
                '-resize', $width.'x'.$height,
            ];

            if ($crop) {
                $cmd[] = '-crop';
                $cmd[] = $crop;
                $cmd[] = '+repage';
            } elseif ($resize) {
                $cmd[] = '-gravity';
                $cmd[] = 'center';
                $cmd[] = '-extent';
                $cmd[] = $width.'x'.$height;
            }

            $cmd[] = $dstPath;

            $process = new Process($cmd);
            $process->mustRun();
        }

        return $this->sendBinaryFileResponse($dstPath);
    }

    public function sendBinaryFileResponse(string $fileName): BinaryFileResponse
    {
        $response = $this->file($fileName);

        $maxAge = 30 * 60 * 60 * 24; // 30 days
        $response->setPublic();
        $response->setMaxAge($maxAge);
        $response->setSharedMaxAge($maxAge);
        $response->setAutoEtag();
        $response->setAutoLastModified();
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');

        return $response;
    }
}
