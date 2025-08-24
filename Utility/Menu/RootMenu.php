<?php

namespace NyroDev\UtilityBundle\Utility\Menu;

class RootMenu extends Menuable
{
    private array $linkRoutePrmReplace = [];

    public function setLinkRoutePrmReplace(string $search, mixed $replace): void
    {
        $this->linkRoutePrmReplace[$search] = $replace;
    }

    public function getLinkRoutePrmReplace(): array
    {
        return $this->linkRoutePrmReplace;
    }

    public function getBeforeChildsTemplate(): ?string
    {
        return '@NyroDevUtility/Menu/_beforeChilds.html.php';
    }

    public function getTemplate(): string
    {
        return '@NyroDevUtility/Menu/rootMenu.html.php';
    }
}
