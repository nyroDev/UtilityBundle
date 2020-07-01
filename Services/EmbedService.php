<?php

namespace NyroDev\UtilityBundle\Services;

class EmbedService extends AbstractService
{
    const CACHE_KEY_URLPARSER = 'urlParser_';

    public function getChacheKey($url, $prefix = self::CACHE_KEY_URLPARSER)
    {
        return $prefix.sha1($url);
    }

    public function clearCache($url)
    {
        if (!$this->container->has('nyrodev_embed_cache')) {
            return true;
        }

        $cache = $this->get('nyrodev_embed_cache');

        return $cache->delete($this->getChacheKey($url, self::CACHE_KEY_URLPARSER));
    }

    public function data($url, $force = false)
    {
        $cache = false;
        if ($this->container->has('nyrodev_embed_cache')) {
            $cache = $this->get('nyrodev_embed_cache');
        }

        $data = [];
        $cacheKey = $this->getChacheKey($url, self::CACHE_KEY_URLPARSER);
        if ($force || !$cache || !$cache->contains($cacheKey)) {
            try {
                $service = \Embed\Embed::create($url, $this->getCreateOptions($url));
                /* @var $service \Embed\Adapters\AdapterInterface */

                if ($service) {
                    $data = [];
                    $fields = [
                        'title',
                        'description',
                        'url',
                        'type',
                        'tags',
                        'image',
                        'imageWidth',
                        'imageHeight',
                        'images',
                        'code',
                        'source',
                        'width',
                        'height',
                        'aspectRatio',
                        'authorName',
                        'authorUrl',
                        'providerIcon',
                        'providerIcons',
                        'providerName',
                        'providerUrl',
                        'publishedTime',
                        'license',
                    ];
                    foreach ($fields as $field) {
                        try {
                            $data[$field] = $service->$field;
                        } catch (\Exception $e) {
                        }
                    }
                    $data['urlEmbed'] = null;

                    if ($data['code'] && 0 === strpos($data['code'], '<iframe')) {
                        if (false !== strpos($data['url'], 'soundcloud.com')) {
                            $data['code'] = str_replace('&', '&amp;', $data['code']);
                        }
                        $dom = new \DOMDocument();
                        $dom->loadHTML($data['code']);
                        $data['urlEmbed'] = $dom->getElementsByTagName('iframe')->item(0)->getAttribute('src');
                        if (false !== strpos($data['urlEmbed'], 'youtube')) {
                            $data['urlEmbed'] .= '&wmode=opaque';
                        }
                    }
                } else {
                    $data = [];
                }

                if ($cache) {
                    $cache->save($cacheKey, $data, 24 * 60 * 60);
                }
            } catch (\Exception $e) {
            }
        } else {
            $data = $cache->fetch($cacheKey);
        }

        return $data;
    }

    protected function getCreateOptions($url)
    {
        $ret = [];
        $ipv4For = $this->getParameter('nyroDev_utility.embed.useIPv4For');
        if ($ipv4For) {
            $useIPv4 = false;
            foreach (explode('|', $ipv4For) as $tmp) {
                $useIPv4 = $useIPv4 || false !== strpos($url, $tmp);
            }
            if ($useIPv4) {
                $ret = [
                    'resolver' => [
                        'options' => [
                            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                        ],
                    ],
                ];
            }
        }

        return $ret;
    }
}
