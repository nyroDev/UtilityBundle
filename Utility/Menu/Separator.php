<?php

namespace NyroDev\UtilityBundle\Utility\Menu;

class Separator extends Menuable
{
    public function getOuterAttrs(): array
    {
        $attrs = parent::getOuterAttrs();

        $attrs['class'] = ($attrs['class'] ?? '').' separator';

        return $attrs;
    }

    public function getTemplate(): string
    {
        return '@NyroDevUtility/Menu/separator.html.php';
    }
}
