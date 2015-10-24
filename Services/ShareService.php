<?php
namespace NyroDev\UtilityBundle\Services;

class ShareService extends AbstractService {

	protected $metas = array();
	protected $metasProp = array();
	
	/**
	 * Set a share meta value
	 * 
	 * @param string $type Meta name
	 * @param string $value Meta value
	 * @param boolean $useProperty Indicates if the default should use property
	 */
	public function set($type, $value, $useProperty = false) {
		$value = preg_replace('/\s\s+/', ' ', $this->trans($value));
		$keys = array();
		$keysProp = array();
		switch($type) {
			case 'title':
				$keysProp[] = 'og:title';
				$keys[] = 'title';
				$keys[] = 'twitter:title';
				break;
			case 'description':
				$keysProp[] = 'og:description';
				$keys[] = 'description';
				$keys[] = 'twitter:description';
				break;
			case 'image':
				$keysProp[] = 'og:image';
				$keys[] = 'twitter:image:src';
				if (strpos($value, '://') === false)
					$value = $this->container->get('nyrodev')->getFullUrl($value);
				break;
			default:
				if ($useProperty)
					$keysProp[] = $type;
				else
					$keys[] = $type;
				break;
		}
		foreach($keys as $k) {
			if ($value) {
				$this->metas[$k] = $value;
				if ($k == 'twitter:image:src')
					$this->metas['twitter:card'] = 'summary_large_image';
			} else if (isset($this->metas[$k])) {
				unset($this->metas[$k]);
				if ($k == 'twitter:image:src' && isset($this->metas['twitter:card']))
					unset($this->metas['twitter:card']);
			}
		}
		foreach($keysProp as $k) {
			if ($value) {
				$this->metasProp[$k] = $value;
			} else if (isset($this->metasProp[$k])) {
				unset($this->metasProp[$k]);
			}
		}
	}
	
	/**
	 * Get a share meta value
	 *
	 * @param string $type Meta name
	 * @return string Meta value
	 */
	public function get($type) {
		$ret = null;
		if ($type == 'image') {
			if (isset($this->metasProp['og:image']))
				$ret = $this->metasProp['og:image'];
			else if ($this->getParameter('nyroDev_utility.share.image'))
				$ret = $this->getParameter('nyroDev_utility.share.image');
		} else {
			if (isset($this->metas[$type]))
				$ret = $this->metas[$type];
			else if ($this->getParameter('nyroDev_utility.share.'.$type))
				$ret = $this->getParameter('nyroDev_utility.share.'.$type);
		}
		return preg_replace('/\s\s+/', ' ', $this->trans($ret));
	}
	
	/**
	 * Set all default values at once
	 *
	 * @param string $title Title
	 * @param string $description Description
	 * @param string|null $image Absolute image URL
	 */
	public function setAll($title, $description, $image = null) {
		$this->setTitle($title);
		$this->setDescription($description);
		if (!is_null($image))
			$this->setImage($image);
	}
	
	/**
	 * Set the share title
	 *
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->set('title', $title);
	}
	
	/**
	 * Get the share title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->get('title');
	}
	
	/**
	 * Set the share description
	 *
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->set('description', $description);
	}
	
	/**
	 * Get the share description
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->get('description');
	}
	
	/**
	 * Set the share keywords
	 *
	 * @param string $keywords
	 */
	public function setKeywords($keywords) {
		$this->set('keywords', $keywords);
	}
	
	/**
	 * Get the share keywords
	 *
	 * @return string
	 */
	public function getKeywords() {
		return $this->get('keywords');
	}
	
	/**
	 * Set the share absolute image URL
	 *
	 * @param string $image Absolute image URL
	 */
	public function setImage($image) {
		$this->set('image', $image);
	}
	
	/**
	 * Get the share absolute image URL
	 *
	 * @return string
	 */
	public function getImage() {
		return $this->get('image');
	}
	
	/**
	 * Get all metas set, to be shown in html header
	 *
	 * @return string metas
	 */
	public function getMetas() {
		$ret = array();
		
		if (!isset($this->metas['title']) && $this->getParameter('nyroDev_utility.share.title'))
			$this->setTitle($this->getParameter('nyroDev_utility.share.title'));
		if (!isset($this->metas['description']) && $this->getParameter('nyroDev_utility.share.description'))
			$this->setDescription($this->getParameter('nyroDev_utility.share.description'));
		if (!isset($this->metas['keywords']) && $this->getParameter('nyroDev_utility.share.keywords'))
			$this->set('keywords', $this->getParameter('nyroDev_utility.share.keywords'));
		if (!isset($this->metasProp['og:image']) && $this->getParameter('nyroDev_utility.share.image'))
			$this->setImage($this->getParameter('nyroDev_utility.share.image'));
		
		if (isset($this->metas['title']) && $this->metas['title']) {
			$ret[] = '<title>'.$this->metas['title'].'</title>';
			unset($this->metas['title']);
		}
		
		foreach($this->metas as $k=>$v)
			$ret[] = '<meta name="'.$k.'" content="'.str_replace('"', '&quot;', $v).'" />';
		foreach($this->metasProp as $k=>$v)
			$ret[] = '<meta property="'.$k.'" content="'.str_replace('"', '&quot;', $v).'" />';
		return implode("\n", $ret);
	}
	
	/**
	 * Get number of share for an URL.
	 * Cache the response of this function!
	 * 
	 * @param string $url
	 * @return array
	 */
	public function getNumberOfShares($url) {
		$urlEncoded = urlencode($url);
		$data = array(
			'facebook'=>0,
			'facebookShare'=>0,
			'facebookLike'=>0,
			'facebookComment'=>0,
			'facebookClick'=>0,
			'twitter'=>0,
			'google'=>0,
			'pinterest'=>0
		);

		// Facebook shares
		try {
			$tmp = file_get_contents('https://api.facebook.com/method/fql.query?format=json&query=select%20%20like_count,share_count,comment_count,click_count,total_count%20from%20link_stat%20where%20url=%22'.$urlEncoded.'%22');
			if ($tmp) {
				$tmpJson = json_decode($tmp, true);
				if (is_array($tmpJson) && count($tmpJson)) {
					if (isset($tmpJson[0]['like_count']))
						$retCache['facebookLike'] = $tmpJson[0]['like_count'];
					if (isset($tmpJson[0]['comment_count']))
						$retCache['facebookComment'] = $tmpJson[0]['comment_count'];
					if (isset($tmpJson[0]['click_count']))
						$retCache['facebookClick'] = $tmpJson[0]['click_count'];
					if (isset($tmpJson[0]['share_count']))
						$retCache['facebookShare'] = $tmpJson[0]['share_count'];
					if (isset($tmpJson[0]['total_count']))
						$retCache['facebook'] = $tmpJson[0]['total_count'];
				}
			}
		} catch (\Exception $e) {}

		// Twitter shares
		try {
			$tmp = file_get_contents('http://urls.api.twitter.com/1/urls/count.json?url='.$urlEncoded);
			if ($tmp) {
				$tmpJson = json_decode($tmp, true);
				if (isset($tmpJson['count']))
					$data['twitter'] = $tmpJson['count'];
			}
		} catch (\Exception $e) {}

		// Google+ shares
		try {
			$tmp = file_get_contents('https://plusone.google.com/_/+1/fastbutton?url='.$urlEncoded);
			if ($tmp) {
				@preg_match_all('#{c: (.*?),#si', $tmp, $matches);
				$ret = isset($matches[1][0]) && strlen($matches[1][0]) > 0 ? trim($matches[1][0]) : 0;
				if(0 != $ret)
					$data['google'] = str_replace('.0', '', $ret);
			}
		} catch (\Exception $e) {}

		// Pinterest shares
		try {
			$tmp = file_get_contents('http://api.pinterest.com/v1/urls/count.json?callback=myCallback&url='.$urlEncoded);
			if ($tmp) {
				$tmp = trim(str_replace('myCallback({', '{', $tmp), ')');
				$tmpJson = json_decode($tmp, true);
				if (isset($tmpJson['count']))
					$data['pinterest'] = $tmpJson['count'];
			}
		} catch (\Exception $e) {}
		
		return $data;
	}
	
	/**
	 * Get the translation for a given keyword
	 *
	 * @param string $key Translation key
	 * @param array $parameters Parameters to replace
	 * @param string $domain Translation domain
	 * @param string $locale Local to use
	 * @return string The translation
	 */
	public function trans($key, array $parameters = array(), $domain = 'messages', $locale = null) {
		return $this->container->get('translator')->trans($key, $parameters, $domain, $locale);
	}
	
}