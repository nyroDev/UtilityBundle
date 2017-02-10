<?php

namespace NyroDev\UtilityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NyroDevUtilityExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
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
            $definition->addTag('translation.loader', array('alias' => 'db'));
            $container->setDefinition('nyrodev_utility.dbLoader', $definition);
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
            foreach ($config['image'] as $k => $v) {
                $container->setParameter('nyroDev_utility.imageService.configs.'.$k, $v);
            }
        }

        if (isset($config['embed']) && is_array($config['embed'])) {
            foreach ($config['embed'] as $k => $v) {
                $container->setParameter('nyroDev_utility.embed.'.$k, $v);
            }
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('forms.yml');
        $loader->load('services_'.$config['db_driver'].'.yml');

        if ('orm' === $config['db_driver']) {
            $managerService = 'nyrodev.entity_manager';
            $doctrineService = 'doctrine';
        } else {
            $managerService = 'nyrodev.document_manager';
            $doctrineService = sprintf('doctrine_%s', $config['db_driver']);
        }
        $definition = $container->getDefinition($managerService);
        if (method_exists($definition, 'setFactory')) {
            $definition->setFactory(array(new Reference($doctrineService), 'getManager'));
        } else {
            $definition->setFactoryService($doctrineService);
            $definition->setFactoryMethod('getManager');
        }
    }
}
