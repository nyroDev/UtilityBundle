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
				'fit'=>true
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
		if (is_array($configKey))
			return $configKey;
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
	
	public function _resize($file, array $config, $force = false, $dest = null) {
		if (is_null($dest)) {
			$info = getimagesize($file);
			$ext = 'jpg';
			switch($info[2]) {
				case IMAGETYPE_GIF:	$ext = 'gif'; break;
				case IMAGETYPE_PNG:	$ext = 'png'; break;
			}
			$cachePath = $this->getCachePath($file);
			$dest = $cachePath.$config['name'].'.'.$ext;
		}
		
		if ($force || !file_exists($dest)) {
			$imgDst = $this->resizeResource($this->createImgSrc($file), $config);
			
			switch($this->get('nyrodev')->getExt($dest)) {
				case 'jpg':
					imagejpeg($imgDst, $dest, isset($config['quality']) ? $config['quality'] : 100);
					break;
				case 'gif':
					imagegif($imgDst, $dest);
					break;
				case 'png':
					$quality = isset($config['quality']) ? $config['quality'] : 100;
					if ($quality > 9)
						$quality = round($quality / 100);
					imagepng($imgDst, $dest, $quality);
					break;
			}
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
		
		if ($config['w'] && isset($config['maxh']) && $config['maxh'] && $config['maxh'] > 0) {
			// Width is fixed
			$tmpH = round($srcH * $scaleW);
			if ($tmpH > $config['maxh']) {
				$config['h'] = $config['maxh'];
				$scaleH = $config['h'] / $srcH;
				$dstH = $config['h'];
			}
		} else if ($config['h'] && isset($config['maxw']) && $config['maxw'] && $config['maxw'] > 0) {
			// Height is fixed
			$tmpW = round($srcW * $scaleH);
			if ($tmpW > $config['maxw']) {
				$config['w'] = $config['maxw'];
				$scaleW = $config['w'] / $srcW;
				$dstW = $config['w'];
			}
		}
		
		if (isset($config['w']) && $config['w'] && isset($config['h']) && $config['h']) {
			// Dimensions are fixed
			if (isset($config['useGivenDimensions']) && $config['useGivenDimensions']) {
				// Change nothing here, just use given settings
			} else if (isset($config['fit']) && $config['fit']) {
				if ($scaleW > $scaleH) {
					$srcH = round($config['h'] / $scaleW);
					$srcY = round(($info['h'] - $srcH) / 2);
				} else {
					$srcW = round($config['w'] / $scaleH);
					$srcX = round(($info['w'] - $srcW) / 2);
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
	
	public function createImgSrc($file, $allowTransparent = true) {
		$info = getimagesize($file);
		$src = null;
		$isTransparent = true;
		switch ($info[2]) {
			case IMAGETYPE_JPEG:
				$src = @imagecreatefromjpeg($file);
				$isTransparent = false;
				break;
			case IMAGETYPE_GIF:
				$src = @imagecreatefromgif($file);
				break;
			case IMAGETYPE_PNG:
				$src = @imagecreatefrompng($file);
				break;
			case IMAGETYPE_BMP:
				$src = $this->imagecreatefrombmp($file);
				break;
		}
		if (!$src)
			throw new \Exception('Error while reading source image: '.$file, 500);

		if ($isTransparent && $allowTransparent) {
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
	
	/**
	 * Create a GD image source from BMP.
	 * http://php.net/manual/fr/function.imagecreatefromwbmp.php#86214
	 *
	 * @param string $filename File path
	 * @return resource
	 */
	protected function imagecreatefrombmp($filename) {
		//    Load the image into a string
		$file    =    fopen($filename, "rb");
		$read    =    fread($file,10);
		while(!feof($file)&&($read<>""))
			$read    .=    fread($file,1024);

		$temp    =    unpack("H*",$read);
		$hex    =    $temp[1];
		$header    =    substr($hex,0,108);

		//    Process the header
		//    Structure: http://www.fastgraph.com/help/bmp_header_format.html
		if (substr($header,0,4)=="424d") {
			//    Cut it in parts of 2 bytes
			$header_parts    =    str_split($header,2);

			//    Get the width        4 bytes
			$width            =    hexdec($header_parts[19].$header_parts[18]);

			//    Get the height        4 bytes
			$height            =    hexdec($header_parts[23].$header_parts[22]);

			//    Unset the header params
			unset($header_parts);
		} 

		//    Define starting X and Y
		$x                =    0;
		$y                =    1;

		//    Create newimage
		$image            =    imagecreatetruecolor($width,$height);

		//    Grab the body from the image
		$body            =    substr($hex,108);

		//    Calculate if padding at the end-line is needed
		//    Divided by two to keep overview.
		//    1 byte = 2 HEX-chars
		$body_size        =    (strlen($body)/2);
		$header_size    =    ($width*$height);

		//    Use end-line padding? Only when needed
		$usePadding        =    ($body_size>($header_size*3)+4);

		//    Using a for-loop with index-calculation instaid of str_split to avoid large memory consumption
		//    Calculate the next DWORD-position in the body
		for ($i=0;$i<$body_size;$i+=3) {
			//    Calculate line-ending and padding
			if ($x>=$width) {
				//    If padding needed, ignore image-padding
				//    Shift i to the ending of the current 32-bit-block
				if ($usePadding)
					$i    +=    $width%4;

				//    Reset horizontal position
				$x    =    0;

				//    Raise the height-position (bottom-up)
				$y++;

				//    Reached the image-height? Break the for-loop
				if ($y>$height)
					break;
			}

			//    Calculation of the RGB-pixel (defined as BGR in image-data)
			//    Define $i_pos as absolute position in the body
			$i_pos    =    $i*2;
			$r        =    hexdec($body[$i_pos+4].$body[$i_pos+5]);
			$g        =    hexdec($body[$i_pos+2].$body[$i_pos+3]);
			$b        =    hexdec($body[$i_pos].$body[$i_pos+1]);

			//    Calculate and draw the pixel
			$color    =    imagecolorallocate($image,$r,$g,$b);
			imagesetpixel($image,$x,$height-$y,$color);

			//    Raise the horizontal position
			$x++;
		}

		//    Unset the body / free the memory
		unset($body);

		//    Return image-object
		return $image;
	}

	/**
	 * Resize images in HTML regarding their width and/or height attributes
	 *
	 * @param string $html HTML content
	 * @param boolean $absolutizeUrl Indicates if the src should be absolutized
	 * @param boolean $addBody Indicates if the content contains body
	 * @return string
	 */
	public function resizeImagesInHtml($html, $absolutizeUrl = false, $addBody = false) {
		if (!$html)
			return $html;
		
		$this->get('nyrodev')->increasePhpLimits();
		$dom = new \DOMDocument();
		//$html = utf8_decode($html);
		if ($addBody)
			$html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>';
		$dom->loadHTML($html);

		$body = $dom->getElementsByTagName('body')->item(0);
		$this->resizeImagesInHtmlDom($body, $absolutizeUrl);
		
		return $addBody ? (str_replace(
				array('<!DOCTYPE html>', '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">', '<head><meta charset="UTF-8"></head>', '<html><body>', '</body></html>'),
				array('', '', '', '', ''),
				$dom->saveHTML())) : $dom->saveHtml();
	}
	
	/**
	 * Resize images in HTML regarding thei width and/or height attributes, internal use
	 *
	 * @param \DOMElement $node
	 * @param boolean $absolutizeUrl Indicates if the src should be absolutized
	 */
	protected function resizeImagesInHtmlDom(\DOMElement $node, $absolutizeUrl = false) {
		if (strtolower($node->tagName) == 'img' && ($node->hasAttribute('width') || $node->hasAttribute('height')) && $node->hasAttribute('src') && strpos($node->getAttribute('src'), 'http') !== 0) {
			// We have everything to resize the imagen let's dot it
			$w = $node->hasAttribute('width') ? $node->getAttribute('width') : null;
			$h = $node->hasAttribute('height') ? $node->getAttribute('height') : null;
			$baseUrl = $this->get('router')->getContext()->getBaseUrl();
			$fileUrl = basename($baseUrl);
			if (strpos($fileUrl, '.php'))
				$baseUrl = dirname($baseUrl);
			
			$src = $node->getAttribute('src');
			$webFile = trim($baseUrl && $baseUrl != '/' ? str_replace($baseUrl, '', $src) : $src, '/');
			$webDir = $this->get('kernel')->getRootDir().'/../web/';
			$file = $webDir.$webFile;
			if (file_exists($file)) {
				$src = str_replace($webDir, '/', $this->_resize($file, array(
					'name'=>$w.'_'.$h,
					'w'=>$w,
					'h'=>$h,
					'useGivenDimensions'=>true
				)));
			}
			if ($absolutizeUrl)
				$src = $this->get('nyrodev')->getFullUrl($src);
			
			$node->setAttribute('src', $src);
		}
		if ($absolutizeUrl && strtolower($node->tagName) == 'a' && $node->hasAttribute('href')) {
			$node->setAttribute('href', $this->get('nyrodev')->getFullUrl($node->getAttribute('href')));
		}
		foreach($node->childNodes as $n) {
			if ($n instanceof \DOMElement) 
				$this->resizeImagesInHtmlDom($n, $absolutizeUrl);
		}
	}
	
}