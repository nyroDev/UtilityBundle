<?php
namespace NyroDev\UtilityBundle\Services;

class ImageService extends AbstractService {

	public function resize($file, $configKey = 'default', $force = false) {
		$resizedPath = $this->_resize($file, $this->getConfig($configKey), $force);
		$tmp = explode('/web/', $resizedPath);
		return $this->container->get('templating.helper.assets')->getUrl($tmp[1]);
	}
	
	public function mask($originalFile, $maskFile, $destName = 'masked', $useMaskSize = true, $force = false) {
		$cachePath = $this->getCachePath($originalFile);
		$destFile = $cachePath.$destName.'.png';
		if ($force || !file_exists($destFile)) {
			$original = $this->createImgSrc($originalFile);
			$mask = $this->createImgSrc($maskFile);

			$config = array(
				'w'=>$useMaskSize ? $mask['info']['w'] : $original['info']['w'],
				'h'=>$useMaskSize ? $mask['info']['h'] : $original['info']['h'],
			);
			$originalResource = $this->resizeResource($original, $config);
			$maskResource = $this->resizeResource($mask, $config);
			
			$dstResource = imagecreatetruecolor($config['w'], $config['h']);
			imagealphablending($dstResource, false);
			imagesavealpha($dstResource, true);
			imagefill($dstResource, 0, 0, imagecolorallocatealpha($dstResource, 0, 0, 0, 127));

			// Perform pixel-based alpha map application
			for ($x = 0; $x < $config['w']; $x++ ){
				for( $y = 0; $y < $config['h']; $y++ ) {
					$alpha = imagecolorsforindex($maskResource, imagecolorat($maskResource, $x, $y));
					$color = imagecolorsforindex($originalResource, imagecolorat($originalResource, $x, $y));
					imagesetpixel($dstResource, $x, $y, imagecolorallocatealpha($dstResource, $color['red'], $color['green'], $color['blue'], $alpha['alpha']));
				}
			}
			imagedestroy($originalResource);
			imagedestroy($maskResource);
			
			imagepng($dstResource, $destFile, 9);
		}
		$tmp = explode('/web/', $destFile);
		return $this->container->get('templating.helper.assets')->getUrl($tmp[1]);
	}
	
	public function getConfig($configKey = 'default') {
		$prm = $this->getParameter('nyroDev_utility.imageService.configs.'.$configKey);
		$prm['name'] = $configKey;
		return $prm;
	}
	
	public function getCachePath($file, $autoCreate = true) {
		$tmp = explode('/web/', $file);
		$dir = $tmp[0].'/web/cache/'.$tmp[1].'/';
		if ($autoCreate && !file_exists($dir))
			mkdir($dir, 0777, true);
		return $dir;
	}
	
	public function removeCache($file) {
		$dir = $this->getCachePath($file, false);
		if (file_exists($dir)) {
			foreach(glob($dir.'*') as $f)
				unlink($f);
			rmdir($dir);
		}
	}
	
	public function _resize($file, array $config, $force = false) {
		$cachePath = $this->getCachePath($file);
		$dest = $cachePath.$config['name'].'.jpg';
		
		if ($force || !file_exists($dest)) {
			$imgDst = $this->resizeResource($this->createImgSrc($file), $config);
			
			imagejpeg($imgDst, $dest, isset($config['quality']) ? $config['quality'] : 100);
			imagedestroy($imgDst);
		}
		return $dest;
	}
	
	public function resizeResource(array $imageData, array $config, $destroySrcResource = true) {
		$src = $imageData['src'];
		$info = $imageData['info'];
		$isTransparent = $imageData['isTransparent'];

		if (!isset($config['w']))
			$config['w'] = 0;

		if (!isset($config['h']))
			$config['h'] = 0;

		$srcW = $info['w'];
		$srcH = $info['h'];
		$srcX = 0;
		$srcY = 0;
		$dstX = 0;
		$dstY = 0;
		$scaleW = $config['w'] / $srcW;
		$scaleH = $config['h'] / $srcH;
		$dstW = $config['w'];
		$dstH = $config['h'];
		if (isset($config['useMaxResize']) && $config['useMaxResize'] && $config['w'] && $config['h']) {
			if ($scaleW > $scaleH) {
				$config['w'] = 0;
			} else {
				$config['h'] = 0;
			}
		}
		if (isset($config['w']) && $config['w'] && isset($config['h']) && $config['h']) {
			// Dimensions are fixed
			if (isset($config['fit']) && $config['fit']) {
				if ($scaleW > $scaleH) {
					$srcH = round($config['h'] / $scaleW);
					$srcY = round(($info['w'] - $srcH) / 2);
				} else {
					$srcW = round($config['w'] / $scaleH);
					$srcX = round(($info['h'] - $srcW) / 2);
				}
			} else {
				if ($scaleW > $scaleH) {
					$dstW = round($srcW * $scaleH);
					$dstX = round(($config['w'] - $dstW) / 2);
				} else {
					$dstH = round($srcH * $scaleW);
					$dstY = round(($config['h'] - $dstH) / 2);
				}
			}
		} else if ($config['w']) {
			// Width is fixed
			$config['h'] = round($srcH * $scaleW);
			$dstH = round($srcH * $scaleW);
			$config['fit'] = true;
		} else if ($config['h']) {
			// Height is fixed
			$config['w'] = round($srcW * $scaleH);
			$dstW = round($srcW * $scaleH);
			$config['fit'] = true;
		} else {
			// No dimensions requested, use the imgAct dimensions
			$config['w'] = $info['w'];
			$config['h'] = $info['h'];
			$dstW = $config['w'];
			$dstH = $config['h'];
		}

		$imgDst = imagecreatetruecolor($config['w'], $config['h']);
		if ($isTransparent) {
			imagealphablending($imgDst, false);
			imagesavealpha($imgDst, true);
		}
		if (empty($config['bgColor']) && $isTransparent) {
			$transparency = imagecolortransparent($imgDst);
			if ($transparency >= 0) {
				$trnprtIndex = imagecolortransparent($imgDst);
				$transparentColor  = imagecolorsforindex($imgDst, $trnprtIndex);
				$transparency      = imagecolorallocate($imgDst, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue']);
				imagefill($imgDst, 0, 0, $transparency);
				imagecolortransparent($imgDst, $transparency);
			} else if ($info['type'] == IMAGETYPE_PNG) {
				imagealphablending($imgDst, false);
				imagesavealpha($imgDst, true);
				imagefill($imgDst, 0, 0, imagecolorallocatealpha($imgDst, 0, 0, 0, 127));
			}
		} else if (!isset($config['fit']) || !$config['fit']) {
			$cl = $this->hexa2dec(isset($config['bgColor']) ? $config['bgColor'] : 'ffffff');
			$clR = imagecolorallocate($imgDst, $cl[0], $cl[1], $cl[2]);
			imagefill($imgDst, 0, 0, $clR);
		}

		imagecopyresampled($imgDst, $src, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
		
		if ($destroySrcResource)
			imagedestroy($src);
		
		return $imgDst;
	}
	
	protected function createImgSrc($file) {
		$info = getimagesize($file);
		$src = null;
		$isTransparent = true;
		switch ($info[2]) {
			case IMAGETYPE_JPEG:
				$src = imagecreatefromjpeg($file);
				$isTransparent = false;
				break;
			case IMAGETYPE_GIF:
				$src = imagecreatefromgif($file);
				break;
			case IMAGETYPE_PNG:
				$src = imagecreatefrompng($file);
				break;
		}
		if (!$src)
			throw new Exception('Error while reading source image: '.$file, 500, 'Error');

		if ($isTransparent) {
			imagealphablending($src, false);
			imagesavealpha($src, true);
		}
		
		return array(
			'src'=>$src,
			'info'=>array(
				'w'=>$info[0],
				'h'=>$info[1],
				'type'=>$info[2]
			),
			'isTransparent'=>$isTransparent
		);
	}
	
	/**
	 * Convert an hexadecimal color to an rgb.
	 *
	 * @param string $col The hexadecimal color
	 * @return array Numeric index (0: R, 1: V and 2: B)
	 */
	protected function hexa2dec($col) {
		return array(
			base_convert(substr($col, 0, 2), 16, 10),
			base_convert(substr($col, 2, 2), 16, 10),
			base_convert(substr($col, 4, 2), 16, 10)
		);
	}

}