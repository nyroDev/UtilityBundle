<?php

namespace NyroDev\UtilityBundle\Utility\Menu;

class RootMenu extends Menuable
{
    public function getBeforeChildsTemplate(): ?string
    {
        return '@NyroDevUtility/Menu/_beforeChilds.html.php';
    }

    public function getTemplate(): string
    {
        return '@NyroDevUtility/Menu/rootMenu.html.php';
    }
}
