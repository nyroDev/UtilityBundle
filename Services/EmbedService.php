<?php

namespace NyroDev\UtilityBundle\Services;

use DOMDocument;
use Embed\Embed;
use Exception;

class EmbedService extends AbstractService
{
    public const CACHE_KEY_URLPARSER = 'urlParser_';

    public function getChacheKey(string $url, $prefix = self::CACHE_KEY_URLPARSER): string
    {
        return $prefix.sha1($url);
    }

    public function clearCache(string $url)
    {
        if (!$this->container->has('nyrodev_embed_cache')) {
            return true;
        }

        $cache = $this->get('nyrodev_embed_cache');

        return $cache->delete($this->getChacheKey($url, self::CACHE_KEY_URLPARSER));
    }

    public function data(string $url, bool $force = false): array
    {
        $cache = false;
        if ($this->container->has('nyrodev_embed_cache')) {
            $cache = $this->get('nyrodev_embed_cache');
        }

        $data = [];
        $cacheKey = $this->getChacheKey($url, self::CACHE_KEY_URLPARSER);
        if ($force || !$cache || !$cache->contains($cacheKey)) {
            try {
                $embed = new Embed();
                $info = $embed->get($url);
                $oEmbedData = $info->getOEmbed()->all();

                if ($info) {
                    $data = [
                        'type' => null,
                    ];
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
                            $data[$field] = $info->$field;
                        } catch (Exception $e) {
                            if (isset($oEmbedData[$field])) {
                                $data[$field] = $oEmbedData[$field];
                            }
                        }
                    }
                    $data['urlEmbed'] = null;

                    if ($data['code'] && 0 === strpos($data['code'], '<iframe')) {
                        if (false !== strpos($data['url'], 'soundcloud.com')) {
                            $data['code'] = str_replace('&', '&amp;', $data['code']);
                        }
                        $dom = new DOMDocument();
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
            } catch (Exception $e) {
            }
        } else {
            $data = $cache->fetch($cacheKey);
        }

        return $data;
    }
}
