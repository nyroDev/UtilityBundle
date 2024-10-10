<?php

namespace NyroDev\UtilityBundle\DependencyInjection;

use NyroDev\UtilityBundle\Services\ShareService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

class NyroDevUtilityExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('nyroDev_utility.db_driver', $config['db_driver']);
        $container->setParameter('nyroDev_utility.model_manager_name', $config['model_manager_name']);
        $container->setParameter('nyroDev_utility.show_edit_id', isset($config['show_edit_id']) && $config['show_edit_id']);

        $container->setParameter('nyroDev_utility.dateFormatUseOffsetDefault', isset($config['dateFormatUseOffsetDefault']) && $config['dateFormatUseOffsetDefault']);
        $container->setParameter('nyroDev_utility.setLocale', isset($config['setLocale']) && $config['setLocale']);
        $container->setParameter('nyroDev_utility.setContentLanguageResponse', isset($config['setContentLanguageResponse']) && $config['setContentLanguageResponse']);
        $container->setParameter('nyroDev_utility.translationDb', isset($config['translationDb']) && $config['translationDb'] ? $config['translationDb'] : false);

        if (isset($config['translationDb']) && $config['translationDb']) {
            $definition = new Definition('NyroDev\UtilityBundle\Loader\DbLoader');
            $definition->addArgument(new Reference('service_container'));
            $definition->addTag('translation.loader', ['alias' => 'db']);
            $container->setDefinition('nyroDev_utility.dbLoader', $definition);
        }

        $container->setParameter('nyroDev_utility.pluploadMaxFileSize', isset($config['pluploadMaxFileSize']) && $config['pluploadMaxFileSize'] ? $config['pluploadMaxFileSize'] : false);

        if (isset($config['browser']) && is_array($config['browser'])) {
            foreach ($config['browser'] as $k => $v) {
                $container->setParameter('nyroDev_utility.browser.'.$k, $v);
            }
        }

        if (isset($config['share']) && is_array($config['share'])) {
            foreach ($config['share'] as $k => $v) {
                $container->setParameter('nyroDev_utility.share.'.$k, $v);
            }
        }

        if (isset($config['image']) && is_array($config['image'])) {
            $shareImageConfig = ShareService::IMAGE_CONFIG_DEFAULT;
            foreach ($config['image'] as $k => $v) {
                if (ShareService::IMAGE_CONFIG_NAME === $k) {
                    $shareImageConfig = array_merge($shareImageConfig, $v);
                } else {
                    $container->setParameter('nyroDev_utility.imageService.configs.'.$k, $v);
                }
            }
            $container->setParameter('nyroDev_utility.imageService.configs.'.ShareService::IMAGE_CONFIG_NAME, $shareImageConfig);
        }

        if (isset($config['redirectIfNotUrl_params']) && is_array($config['redirectIfNotUrl_params'])) {
            $container->setParameter('nyroDev_utility.redirectIfNotUrl_params', $config['redirectIfNotUrl_params']);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('forms.yaml');
        $loader->load('services_'.$config['db_driver'].'.yaml');

        if ('orm' === $config['db_driver']) {
            $managerService = 'nyrodev.entity_manager';
            $doctrineService = 'doctrine';
        } else {
            $managerService = 'nyrodev.document_manager';
            $doctrineService = sprintf('doctrine_%s', $config['db_driver']);
        }
        $definition = $container->getDefinition($managerService);
        $definition->setFactory([new Reference($doctrineService), 'getManager']);

        // Load commands
        $definition = new Definition();
        $definition
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setPublic(false)
        ;
        $dirLoader = new Loader\DirectoryLoader($container, new FileLocator(__DIR__.'/../Command'));
        $dirLoader->registerClasses($definition, 'NyroDev\\UtilityBundle\\Command\\', './*');

        // Load controllers
        $definition = new Definition();
        $definition
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->addMethodCall('setContainer', [new Reference('service_container')])
            ->addTag('controller.service_arguments')
        ;
        $dirLoader = new Loader\DirectoryLoader($container, new FileLocator(__DIR__.'/../Controller'));
        $dirLoader->registerClasses($definition, 'NyroDev\\UtilityBundle\\Controller\\', './*');
    }
}
