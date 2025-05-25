<?php

namespace NyroDev\UtilityBundle\Twig;

use NyroDev\UtilityBundle\Helper\IconHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class IconExtension extends AbstractExtension
{
    public function __construct(
        private readonly IconHelper $iconHelper,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('nyrodev_icon', [$this->iconHelper, 'getIcon']),
        ];
    }
}
