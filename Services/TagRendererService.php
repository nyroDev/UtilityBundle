<?php

namespace NyroDev\UtilityBundle\Services;

use NyroDev\UtilityBundle\Services\Traits\AssetsPackagesServiceableTrait;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

class TagRendererService extends AbstractService
{
    use AssetsPackagesServiceableTrait;

    private $entrypointLookupCollection;

    public function __construct(EntrypointLookupCollection $entrypointLookupCollection)
    {
        $this->entrypointLookupCollection = $entrypointLookupCollection;
    }

    public function reset(string $entrypointName = '_default')
    {
        $this->getEntrypointLookup($entrypointName)->reset();
    }

    public function renderWebpackScriptTags(string $entryName, string $moreAttrs = null, string $packageName = null, string $entrypointName = '_default'): string
    {
        $scriptTags = [];
        foreach ($this->getScriptFiles($entryName, $packageName, $entrypointName) as $filename) {
            $scriptTags[] = sprintf(
                '<script src="%s"'.($moreAttrs ? ' '.$moreAttrs : null).'></script>',
                $filename
            );
        }

        return
            $this->renderWebpackLinkTags($entryName, null, $packageName, $entrypointName)
            .implode('', $scriptTags);
    }

    public function getScriptFiles(string $entryName, string $packageName = null, string $entrypointName = '_default'): array
    {
        $scriptFiles = [];
        foreach ($this->getEntrypointLookup($entrypointName)->getJavaScriptFiles($entryName) as $filename) {
            $scriptFiles[] = htmlentities($this->getAssetPath($filename, $packageName));
        }

        return $scriptFiles;
    }

    public function renderWebpackLinkTags(string $entryName, string $moreAttrs = null, string $packageName = null, string $entrypointName = '_default'): string
    {
        $linkTags = [];
        foreach ($this->getLinkFiles($entryName, $packageName, $entrypointName) as $filename) {
            $linkTags[] = sprintf(
                '<link rel="stylesheet" href="%s"'.($moreAttrs ? ' '.$moreAttrs : null).' />',
                $filename
            );
        }

        return implode('', $linkTags);
    }

    public function getLinkFiles(string $entryName, string $packageName = null, string $entrypointName = '_default'): array
    {
        $linkFiles = [];
        foreach ($this->getEntrypointLookup($entrypointName)->getCssFiles($entryName) as $filename) {
            $linkFiles[] = htmlentities($this->getAssetPath($filename, $packageName));
        }

        return $linkFiles;
    }

    private function getAssetPath(string $assetPath, string $packageName = null): string
    {
        if (null === $this->getAssetsPackages()) {
            throw new \Exception('To render the script or link tags, run "composer require symfony/asset".');
        }

        return $this->getAssetsPackages()->getUrl(
            $assetPath,
            $packageName
        );
    }

    private function getEntrypointLookup(string $buildName): EntrypointLookupInterface
    {
        return $this->entrypointLookupCollection->getEntrypointLookup($buildName);
    }
}
