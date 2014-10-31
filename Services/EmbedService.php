<?php
namespace NyroDev\UtilityBundle\Services;

class EmbedService extends AbstractService {

	public function getChacheKey($url, $prefix = '') {
		return $prefix.sha1($url);
	}
	
	public function data($url, $force = false) {
		$cache = $this->get('winzou_cache');
		/* @var $cache \winzou\CacheBundle\Cache\LifetimeFileCache */
		
		$cacheKey = $this->getChacheKey($url, 'embedUrlParser_');
		if ($force || !$cache->contains($cacheKey)) {
			
			$this->setDefaultResolver($url);
			
			$service = \Embed\Embed::create($url);
			/* @var $service \Embed\Adapters\AdapterInterface */
			
			if ($service) {
				$data = array(
					'type'=>$service->getType(),
					'url'=>$service->getUrl(),
					'title'=>$service->getTitle(),
					'description'=>$service->getDescription(),
					'image'=>$service->getImage(),
					'code'=>$service->getCode(),
					'width'=>$service->getWidth(),
					'height'=>$service->getHeight(),
					'aspectRatio'=>$service->getAspectRatio(),
					'urlEmbed'=>null
				);
				if ($data['code'] && strpos($data['code'], '<iframe') === 0) {
					if (strpos($data['url'], 'soundcloud.com') !== false)
						$data['code'] = str_replace('&', '&amp;', $data['code']);
					$dom = new \DOMDocument();
					$dom->loadHTML($data['code']);
					$data['urlEmbed'] = $dom->getElementsByTagName('iframe')->item(0)->getAttribute('src');
					if (strpos($data['urlEmbed'], 'youtube') !== false)
						$data['urlEmbed'].= '&wmode=opaque';
				}
			} else {
				$data = array();
			}
			
			$cache->save($cacheKey, $data, 24 * 60 * 60);
		} else {
			$data = $cache->fetch($cacheKey);
		}
		return $data;
	}
	
	protected function setDefaultResolver($url) {
		$ipv4For = $this->getParameter('nyroDev_utility.embed.useIPv4For');
		if ($ipv4For) {
			$useIPv4 = false;
			foreach(explode('|', $ipv4For) as $tmp)
				$useIPv4 = $useIPv4 || strpos($url, $tmp) !== false;
			if ($useIPv4)
				\Embed\Request::setDefaultResolver('NyroDev\\UtilityBundle\\Embed\\RequestResolvers\\CurlIPv4');
			else
				\Embed\Request::setDefaultResolver('Embed\\RequestResolvers\\Curl');
		}
	}

}

