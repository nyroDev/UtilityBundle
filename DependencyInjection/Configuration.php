<?php

namespace NyroDev\UtilityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('nyro_dev_utility');
		
		$rootNode
			->children()
				->booleanNode('setLocale')->defaultFalse()->end()
				->arrayNode('browser')
					->addDefaultsIfNotSet()
					->children()
						->booleanNode('defaultEnable')->defaultFalse()->end()
						->scalarNode('defaultRoute')->defaultValue('')->end()
						->booleanNode('allowAddDir')->defaultFalse()->end()
					->end()
				->end()
				->arrayNode('share')
					->addDefaultsIfNotSet()
					->children()
						->scalarNode('title')->defaultValue('')->end()
						->scalarNode('description')->defaultValue('')->end()
						->scalarNode('keywords')->defaultValue('')->end()
						->scalarNode('image')->defaultValue('')->end()
					->end()
				->end()
				->arrayNode('image')
					->prototype('array')
						->children()
							->integerNode('w')->defaultValue(0)->end()
							->integerNode('h')->defaultValue(0)->end()
							->integerNode('maxw')->defaultValue(0)->end()
							->integerNode('maxh')->defaultValue(0)->end()
							->scalarNode('quality')->defaultValue(80)->end()
							->booleanNode('fit')->defaultTrue()->end()
							->booleanNode('useMaxResize')->defaultFalse()->end()
							->scalarNode('bgColor')->defaultValue('ffffff')->end()
						->end()
					->end()
				->end()
				->arrayNode('embed')
					->addDefaultsIfNotSet()
					->children()
						->scalarNode('useIPv4For')->defaultValue('youtube.com')->end()
					->end()
				->end()
			->end();

        return $treeBuilder;
    }
}
