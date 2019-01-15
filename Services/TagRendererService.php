<?php

namespace NyroDev\UtilityBundle\Services;

use Psr\Container\ContainerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

class TagRendererService extends AbstractService
{
    private $entrypointLookup;

    private $packages;

    public function __construct(ContainerInterface $container, EntrypointLookupInterface $entrypointLookup, Packages $packages)
    {
        parent::__construct($container);
        $this->entrypointLookup = $entrypointLookup;
        $this->packages = $packages;
    }

    public function renderWebpackScriptTags(string $entryName, string $moreAttrs = null, string $packageName = null): string
    {
        $scriptTags = [];
        foreach ($this->entrypointLookup->getJavaScriptFiles($entryName) as $filename) {
            $scriptTags[] = sprintf(
                '<script src="%s"'.($moreAttrs ? ' '.$moreAttrs : null).'></script>',
                htmlentities($this->getAssetPath($filename, $packageName))
            );
        }

        return implode('', $scriptTags);
    }

    public function renderWebpackLinkTags(string $entryName, string $moreAttrs = null, string $packageName = null): string
    {
        $scriptTags = [];
        foreach ($this->entrypointLookup->getCssFiles($entryName) as $filename) {
            $scriptTags[] = sprintf(
                '<link rel="stylesheet" href="%s"'.($moreAttrs ? ' '.$moreAttrs : null).' />',
                htmlentities($this->getAssetPath($filename, $packageName))
            );
        }

        return implode('', $scriptTags);
    }

    private function getAssetPath(string $assetPath, string $packageName = null): string
    {
        if (null === $this->packages) {
            throw new \Exception('To render the script or link tags, run "composer require symfony/asset".');
        }

        return $this->packages->getUrl(
            '/'.$assetPath, // Is the / needed ?
            $packageName
        );
    }
}
