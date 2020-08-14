<?php

namespace NyroDev\UtilityBundle\Form\Type;

use NyroDev\UtilityBundle\Services\Traits\ContainerInterfaceServiceableTrait;
use Symfony\Component\Form\AbstractType as SrcAbstractType;

abstract class AbstractType extends SrcAbstractType
{
    use ContainerInterfaceServiceableTrait;
}
