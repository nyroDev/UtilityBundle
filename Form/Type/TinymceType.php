<?php

namespace NyroDev\UtilityBundle\Form\Type;

use NyroDev\UtilityBundle\Services\NyrodevService;
use NyroDev\UtilityBundle\Services\Traits\AssetsPackagesServiceableTrait;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TinymceType extends AbstractType
{
    use AssetsPackagesServiceableTrait;

    public const OPTION_TINYMCE_BROWSER = 'tinymceBrowser';
    public const OPTION_TINYMCE = 'tinymce';
    public const OPTION_TINYMCE_PLUGINS = 'tinymcePlugins';

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $attrs = $view->vars['attr'];
        if (!is_array($attrs)) {
            $attrs = [];
        }

        $attrs['class'] = 'tinymce'.(isset($attrs['class']) && $attrs['class'] ? ' '.$attrs['class'] : '');
        $attrs['data-tinymce-url'] = $this->getAssetsPackages()->getUrl('tinymce/tinymce.min.js');

        $tinymceOptions = [
            'language' => $this->container->get(NyrodevService::class)->getRequest()->getLocale(),
            'height' => 450,
            'width' => 720,
            'skin' => 'tinymce-5',
            'plugins' => 'anchor,autolink,charmap,code,fullscreen,image,insertdatetime,link,lists,advlist,media,nonbreaking,preview,searchreplace,table,visualblocks,visualchars,wordcount'.(isset($options[self::OPTION_TINYMCE_PLUGINS]) && $options[self::OPTION_TINYMCE_PLUGINS] ? ','.$options[self::OPTION_TINYMCE_PLUGINS] : null),
            'toolbar' => 'undo redo | styles | bold italic | removeformat | alignleft aligncenter alignright alignjustify | link unlink | image media | fullscreen | bullist numlist outdent indent',
            'menubar' => 'insert edit view table tools',
            'relative_urls' => false,
            'branding' => false,
            'promotion' => false,
            'license_key' => 'gpl',
            'browser_spellcheck' => true,
            'contextmenu' => false,
        ];

        if ((isset($options[self::OPTION_TINYMCE_BROWSER]) && $options[self::OPTION_TINYMCE_BROWSER]) || ($this->container->hasParameter('nyroDev_utility.browser.defaultEnable') && $this->container->getParameter('nyroDev_utility.browser.defaultEnable'))) {
            $canBrowse = isset($options[self::OPTION_TINYMCE_BROWSER]['url']) || ($this->container->hasParameter('nyroDev_utility.browser.defaultRoute') && $this->container->getParameter('nyroDev_utility.browser.defaultRoute'));
            if ($canBrowse) {
                $tinymceOptions['plugins'] .= ',filemanager';

                $tinymceOptions['external_filemanager_path'] = (isset($options[self::OPTION_TINYMCE_BROWSER]['url'])
                    ? $options[self::OPTION_TINYMCE_BROWSER]['url']
                    : $this->container->get(NyrodevService::class)->generateUrl(
                        $this->container->getParameter('nyroDev_utility.browser.defaultRoute'),
                        ['type' => '_TYPE_']
                    ));
                $tinymceOptions['filemanager_title'] = isset($options[self::OPTION_TINYMCE_BROWSER]['title']) ? $options[self::OPTION_TINYMCE_BROWSER]['title'] : $this->container->get('translator')->trans('nyrodev.browser.title');
            }
        }

        if (isset($options[self::OPTION_TINYMCE]) && is_array($options[self::OPTION_TINYMCE])) {
            $tinymceOptions = array_merge($tinymceOptions, $options[self::OPTION_TINYMCE]);
        }

        $attrs['data-tinymce-options'] = json_encode($tinymceOptions);

        $view->vars['attr'] = $attrs;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::OPTION_TINYMCE_BROWSER => [],
            self::OPTION_TINYMCE => [],
            self::OPTION_TINYMCE_PLUGINS => null,
        ]);
    }

    public function getParent(): ?string
    {
        return TextareaType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'tinymce';
    }
}
