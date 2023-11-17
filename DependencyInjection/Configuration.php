<?php

namespace NyroDev\UtilityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('nyro_dev_utility');
        $rootNode = $builder->getRootNode($builder, 'nyro_dev_utility');

        $supportedDrivers = ['orm', 'mongodb'];

        $rootNode
            ->children()
                ->scalarNode('db_driver')
                    ->validate()
                        ->ifNotInArray($supportedDrivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode($supportedDrivers))
                    ->end()
                    ->cannotBeOverwritten()
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('model_manager_name')->defaultNull()->end()
                ->booleanNode('show_edit_id')->defaultTrue()->end()
                ->booleanNode('dateFormatUseOffsetDefault')->defaultFalse()->end()
                ->booleanNode('setLocale')->defaultFalse()->end()
                ->booleanNode('setContentLanguageResponse')->defaultFalse()->end()
                ->scalarNode('translationDb')->defaultValue('')->end()
                ->arrayNode('browser')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('defaultEnable')->defaultFalse()->end()
                        ->scalarNode('defaultRoute')->defaultValue('')->end()
                        ->booleanNode('allowAddDir')->defaultFalse()->end()
                    ->end()
                ->end()
                ->scalarNode('pluploadMaxFileSize')->defaultValue('')->end()
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
                            ->scalarNode('center')->defaultValue('CC')->end() // First value is horizontal (L, C, R), second is vertical (T, C, B)
                            ->booleanNode('tile')->defaultFalse()->end() // Used with center to create 9 tiles from original image
                            ->booleanNode('useMaxResize')->defaultFalse()->end()
                            ->booleanNode('useGivenDimensions')->defaultFalse()->end()
                            ->booleanNode('dontResizeSmaller')->defaultFalse()->end()
                            ->scalarNode('bgColor')->defaultValue('ffffff')->end()
                            ->booleanNode('ignoreAnimatedGif')->defaultFalse()->end()
                            ->arrayNode('filters')
                                ->defaultValue([])
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('redirectIfNotUrl_params')
                    ->defaultValue(['utm_medium', 'utm_source', 'utm_campaign', 'utm_content', 'utm_term'])
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $builder;
    }
}
