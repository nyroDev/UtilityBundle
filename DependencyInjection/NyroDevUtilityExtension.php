<?php

namespace NyroDev\UtilityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NyroDevUtilityExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

		$container->setParameter('nyroDev_utility.setLocale', isset($config['setLocale']) && $config['setLocale']);
		$container->setParameter('nyroDev_utility.setContentLanguageResponse', isset($config['setContentLanguageResponse']) && $config['setContentLanguageResponse']);
		$container->setParameter('nyroDev_utility.translationDb', isset($config['translationDb']) && $config['translationDb'] ? $config['translationDb'] : false);
		
		if (isset($config['translationDb']) && $config['translationDb']) {
			$definition = new Definition('NyroDev\UtilityBundle\Loader\DbLoader');
			$definition->addArgument(new Reference('service_container'));
			$definition->addTag('translation.loader', array('alias'=>'db'));
			$container->setDefinition('nyrodev_utility.dbLoader', $definition);
		}

		if (isset($config['browser']) && is_array($config['browser'])) {
			foreach($config['browser'] as $k=>$v) {
				$container->setParameter('nyroDev_utility.browser.'.$k, $v);
			}
		}

		if (isset($config['share']) && is_array($config['share'])) {
			foreach($config['share'] as $k=>$v) {
				$container->setParameter('nyroDev_utility.share.'.$k, $v);
			}
		}
		
		if (isset($config['image']) && is_array($config['image'])) {
			foreach($config['image'] as $k=>$v) {
				$container->setParameter('nyroDev_utility.imageService.configs.'.$k, $v);
			}
		}
		
		if (isset($config['embed']) && is_array($config['embed'])) {
			foreach($config['embed'] as $k=>$v) {
				$container->setParameter('nyroDev_utility.embed.'.$k, $v);
			}
		}
		
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
