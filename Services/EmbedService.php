<?php
namespace NyroDev\UtilityBundle\Services;

class EmbedService extends AbstractService {

	public function getChacheKey($url, $prefix = '') {
		return $prefix.sha1($url);
	}
	
	public function data($url, $force = false) {
		$cache = false;
		if ($this->container->has('nyrodev_embed_cache'))
			$cache = $this->get('nyrodev_embed_cache');
		
		$data = array();
		$cacheKey = $this->getChacheKey($url, 'urlParser_');
		if ($force || !$cache || !$cache->contains($cacheKey)) {
			try {
				$service = \Embed\Embed::create($url, $this->getCreateOptions($url));
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

				if ($cache)
					$cache->save($cacheKey, $data, 24 * 60 * 60);
			} catch (\Exception $e) {}
			
		} else {
			$data = $cache->fetch($cacheKey);
		}
		return $data;
	}
	
	protected function getCreateOptions($url) {
		$ret = array();
		$ipv4For = $this->getParameter('nyroDev_utility.embed.useIPv4For');
		if ($ipv4For) {
			$useIPv4 = false;
			foreach(explode('|', $ipv4For) as $tmp)
				$useIPv4 = $useIPv4 || strpos($url, $tmp) !== false;
			if ($useIPv4) {
				$ret = array(
					'resolver'=>array(
						'options'=>array(
							CURLOPT_IPRESOLVE=>CURL_IPRESOLVE_V4
						)
					)
				);
			}
		}
		return $ret;
	}

}

