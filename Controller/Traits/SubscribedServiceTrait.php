<?php

namespace NyroDev\UtilityBundle\Controller\Traits;

use NyroDev\PhpTemplateBundle\Helper\TagRendererHelper;
use NyroDev\UtilityBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Services\EmbedService;
use NyroDev\UtilityBundle\Services\FormFilterService;
use NyroDev\UtilityBundle\Services\FormService;
use NyroDev\UtilityBundle\Services\ImageService;
use NyroDev\UtilityBundle\Services\MemberService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use NyroDev\UtilityBundle\Services\ShareService;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use UnitEnum;

use function get_class;

trait SubscribedServiceTrait
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'translator' => '?'.TranslatorInterface::class,
            TranslatorInterface::class => '?'.TranslatorInterface::class,
            'validator' => '?'.ValidatorInterface::class,
            ValidatorInterface::class => '?'.ValidatorInterface::class,
            'kernel' => '?'.KernelInterface::class,
            KernelInterface::class => '?'.KernelInterface::class,
            'event_dispatcher' => '?'.EventDispatcherInterface::class,
            EventDispatcherInterface::class => '?'.EventDispatcherInterface::class,
            'nyrodev' => '?'.NyrodevService::class,
            NyrodevService::class => '?'.NyrodevService::class,
            'nyrodev_image' => '?'.ImageService::class,
            ImageService::class => '?'.ImageService::class,
            'nyrodev_member' => '?'.MemberService::class,
            MemberService::class => '?'.MemberService::class,
            'nyrodev_embed' => '?'.EmbedService::class,
            EmbedService::class => '?'.EmbedService::class,
            'nyrodev_form' => '?'.FormService::class,
            FormService::class => '?'.FormService::class,
            'nyrodev_formFilter' => '?'.FormFilterService::class,
            FormFilterService::class => '?'.FormFilterService::class,
            'nyrodev_tagRenderer' => '?'.TagRendererHelper::class,
            TagRendererHelper::class => '?'.TagRendererHelper::class,
            'nyrodev_share' => '?'.ShareService::class,
            ShareService::class => '?'.ShareService::class,
            'nyrodev_db' => '?'.DbAbstractService::class,
            DbAbstractService::class => '?'.DbAbstractService::class,
        ]);
    }

    protected function getParameter(string $name, $default = null): array|bool|string|int|float|UnitEnum|null
    {
        if (!$this->has(NyrodevService::class)) {
            throw new ServiceNotFoundException(NyrodevService::class, null, null, [], sprintf('The "%s::getParameter()" method is missing a parameter bag to work properly. Did you forget to register your controller as a service subscriber? This can be fixed either by using autoconfiguration or by manually wiring a "parameter_bag" in the service locator passed to the controller.', get_class($this)));
        }

        return $this->get(NyrodevService::class)->getParameter($name, $default);
    }

    protected function hasParameter(string $name)
    {
        if (!$this->has(NyrodevService::class)) {
            throw new ServiceNotFoundException(NyrodevService::class, null, null, [], sprintf('The "%s::getParameter()" method is missing a parameter bag to work properly. Did you forget to register your controller as a service subscriber? This can be fixed either by using autoconfiguration or by manually wiring a "parameter_bag" in the service locator passed to the controller.', get_class($this)));
        }

        return $this->get(NyrodevService::class)->hasParameter($name);
    }
}
