<?php

namespace NyroDev\UtilityBundle\Services;

use Exception;
use NyroDev\UtilityBundle\Model\Sharable;

use function file_exists;

class ShareService extends AbstractService
{
    public const IMAGE_CONFIG_NAME = 'shareImage';
    public const IMAGE_CONFIG_DEFAULT = [
        'name' => 'shareImage',
        'w' => 1000,
        'h' => null,
        'fit' => true,
        'quality' => 80,
    ];

    protected array $metas = [];
    protected array $metasProp = [];

    public function setValue(string $type, string $value, bool $useProperty = false): void
    {
        $value = preg_replace('/\s\s+/', ' ', $this->trans($value));
        $keys = [];
        $keysProp = [];
        switch ($type) {
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
                if (false === strpos($value, '://')) {
                    $value = $this->get(NyrodevService::class)->getFullUrl($value);
                }
                break;
            default:
                if ($useProperty) {
                    $keysProp[] = $type;
                } else {
                    $keys[] = $type;
                }
                break;
        }
        foreach ($keys as $k) {
            if ($value) {
                $this->metas[$k] = $value;
                if ('twitter:image:src' == $k) {
                    $this->metas['twitter:card'] = 'summary_large_image';
                }
            } elseif (isset($this->metas[$k])) {
                unset($this->metas[$k]);
                if ('twitter:image:src' == $k && isset($this->metas['twitter:card'])) {
                    unset($this->metas['twitter:card']);
                }
            }
        }
        foreach ($keysProp as $k) {
            if ($value) {
                $this->metasProp[$k] = $value;
            } elseif (isset($this->metasProp[$k])) {
                unset($this->metasProp[$k]);
            }
        }
    }

    public function getValue(string $type): string
    {
        $ret = null;
        if ('image' == $type) {
            if (isset($this->metasProp['og:image'])) {
                $ret = $this->metasProp['og:image'];
            } elseif ($this->getParameter('nyroDev_utility.share.image')) {
                $ret = $this->getParameter('nyroDev_utility.share.image');
            }
        } else {
            if (isset($this->metas[$type])) {
                $ret = $this->metas[$type];
            } elseif ($this->getParameter('nyroDev_utility.share.'.$type)) {
                $ret = $this->getParameter('nyroDev_utility.share.'.$type);
            }
        }

        return preg_replace('/\s\s+/', ' ', $this->trans($ret));
    }

    /**
     * Set all default values at once.
     */
    public function setAll(string $title, string $description, ?string $image = null): void
    {
        $this->setTitle($title);
        $this->setDescription($description);
        if (!is_null($image)) {
            $this->setImage($image);
        }
    }

    /**
     * Set Sharable values from object.
     */
    public function setSharable(Sharable $sharable, bool $useToStringTitle = true): void
    {
        $nyrodev = $this->get(NyrodevService::class);

        if ($useToStringTitle) {
            $this->setTitle($nyrodev->inlineText($sharable.''));
        }

        if ($sharable->getMetaTitle()) {
            $this->setTitle($nyrodev->inlineText($sharable->getMetaTitle()));
        }
        if ($sharable->getOgTitle()) {
            $this->setValue('og:title', $nyrodev->inlineText($sharable->getOgTitle()), true);
            $this->setValue('twitter:title', $nyrodev->inlineText($sharable->getOgTitle()));
        }

        if ($sharable->getMetaDescription()) {
            $this->setDescription($nyrodev->inlineText($sharable->getMetaDescription()));
        }
        if ($sharable->getOgDescription()) {
            $this->setValue('og:description', $nyrodev->inlineText($sharable->getOgDescription()), true);
            $this->setValue('twitter:description', $nyrodev->inlineText($sharable->getOgDescription()));
        }

        if ($sharable->getMetaKeywords()) {
            $this->setKeywords($nyrodev->inlineText($sharable->getMetaKeywords()));
        }

        if ($sharable->getShareOgImage()) {
            $image = $sharable->getShareOgImage();
            if (false !== strpos($image, '/public/') && file_exists($image)) {
                // This is a full path name, resize it
                $image = $this->get(ImageService::class)->resize($image, self::IMAGE_CONFIG_NAME);
            }
            $this->setImage($image);
        }

        if ($sharable->getShareOthers() && count($sharable->getShareOthers())) {
            foreach ($sharable->getShareOthers() as $k => $v) {
                if (is_array($v)) {
                    $this->setValue($k, $nyrodev->inlineText($v[0]), $v[1]);
                } else {
                    $this->setValue($k, $nyrodev->inlineText($v));
                }
            }
        }
    }

    public function setTitle(string $title): void
    {
        $this->setValue('title', $title);
    }

    public function getTitle(): string
    {
        return $this->getValue('title');
    }

    public function setDescription(string $description): void
    {
        $this->setValue('description', $description);
    }

    public function getDescription(): string
    {
        return $this->getValue('description');
    }

    public function setKeywords(string $keywords): void
    {
        $this->setValue('keywords', $keywords);
    }

    public function getKeywords(): stirng
    {
        return $this->getValue('keywords');
    }

    public function setImage(string $image, bool $getAndSetDimensions = true): void
    {
        $this->setValue('image', $image);
        if ($getAndSetDimensions) {
            $imageSize = $this->get(ImageService::class)->getImageSize($image);
            if (is_array($imageSize) && count($imageSize)) {
                $this->setValue('og:image:width', $imageSize[0], true);
                $this->setValue('og:image:height', $imageSize[1], true);
            }
        }
    }

    public function getImage(): string
    {
        return $this->getValue('image');
    }

    public function getMetas(): string
    {
        $ret = [];

        if (!isset($this->metas['title']) && $this->getParameter('nyroDev_utility.share.title')) {
            $this->setTitle($this->getParameter('nyroDev_utility.share.title'));
        }
        if (!isset($this->metas['description']) && $this->getParameter('nyroDev_utility.share.description')) {
            $this->setDescription($this->getParameter('nyroDev_utility.share.description'));
        }
        if (!isset($this->metas['keywords']) && $this->getParameter('nyroDev_utility.share.keywords')) {
            $this->setValue('keywords', $this->getParameter('nyroDev_utility.share.keywords'));
        }
        if (!isset($this->metasProp['og:image']) && $this->getParameter('nyroDev_utility.share.image')) {
            $this->setImage($this->getParameter('nyroDev_utility.share.image'));
        }

        if (isset($this->metas['title']) && $this->metas['title']) {
            $ret[] = '<title>'.$this->metas['title'].'</title>';
            unset($this->metas['title']);
        }

        foreach ($this->metas as $k => $v) {
            $ret[] = '<meta name="'.$k.'" content="'.str_replace('"', '&quot;', $v).'" />';
        }
        foreach ($this->metasProp as $k => $v) {
            $ret[] = '<meta property="'.$k.'" content="'.str_replace('"', '&quot;', $v).'" />';
        }

        return implode("\n", $ret);
    }

    /**
     * Get number of share for an URL.
     * Cache the response of this function!
     */
    public function getNumberOfShares(string $url): array
    {
        $urlEncoded = urlencode($url);
        $data = [
            'facebook' => 0,
            'facebookShare' => 0,
            'facebookLike' => 0,
            'facebookComment' => 0,
            'facebookClick' => 0,
            'twitter' => 0,
            'google' => 0,
            'pinterest' => 0,
        ];

        // Facebook shares
        try {
            $tmp = file_get_contents('https://api.facebook.com/method/fql.query?format=json&query=select%20%20like_count,share_count,comment_count,click_count,total_count%20from%20link_stat%20where%20url=%22'.$urlEncoded.'%22');
            if ($tmp) {
                $tmpJson = json_decode($tmp, true);
                if (is_array($tmpJson) && count($tmpJson)) {
                    if (isset($tmpJson[0]['like_count'])) {
                        $retCache['facebookLike'] = $tmpJson[0]['like_count'];
                    }
                    if (isset($tmpJson[0]['comment_count'])) {
                        $retCache['facebookComment'] = $tmpJson[0]['comment_count'];
                    }
                    if (isset($tmpJson[0]['click_count'])) {
                        $retCache['facebookClick'] = $tmpJson[0]['click_count'];
                    }
                    if (isset($tmpJson[0]['share_count'])) {
                        $retCache['facebookShare'] = $tmpJson[0]['share_count'];
                    }
                    if (isset($tmpJson[0]['total_count'])) {
                        $retCache['facebook'] = $tmpJson[0]['total_count'];
                    }
                }
            }
        } catch (Exception $e) {
        }

        // Twitter shares
        try {
            $tmp = file_get_contents('http://urls.api.twitter.com/1/urls/count.json?url='.$urlEncoded);
            if ($tmp) {
                $tmpJson = json_decode($tmp, true);
                if (isset($tmpJson['count'])) {
                    $data['twitter'] = $tmpJson['count'];
                }
            }
        } catch (Exception $e) {
        }

        // Google+ shares
        try {
            $tmp = file_get_contents('https://plusone.google.com/_/+1/fastbutton?url='.$urlEncoded);
            if ($tmp) {
                @preg_match_all('#{c: (.*?),#si', $tmp, $matches);
                $ret = isset($matches[1][0]) && strlen($matches[1][0]) > 0 ? trim($matches[1][0]) : 0;
                if (0 != $ret) {
                    $data['google'] = str_replace('.0', '', $ret);
                }
            }
        } catch (Exception $e) {
        }

        // Pinterest shares
        try {
            $tmp = file_get_contents('http://api.pinterest.com/v1/urls/count.json?callback=myCallback&url='.$urlEncoded);
            if ($tmp) {
                $tmp = trim(str_replace('myCallback({', '{', $tmp), ')');
                $tmpJson = json_decode($tmp, true);
                if (isset($tmpJson['count'])) {
                    $data['pinterest'] = $tmpJson['count'];
                }
            }
        } catch (Exception $e) {
        }

        return $data;
    }
}
