<?php

namespace NyroDev\UtilityBundle\Utility\Menu;

class Text extends Menuable
{
    public function __construct(
        public string $content,
        public string $icon = '',
    ) {
    }

    public function getTemplate(): string
    {
        return '@NyroDevUtility/Menu/text.html.php';
    }
}
