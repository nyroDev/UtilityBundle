<?php

namespace NyroDev\UtilityBundle\Utility\Menu;

interface MenuActivableInterface
{
    /**
     * Check if the menu item is active.
     */
    public function isActive(): bool;
}
