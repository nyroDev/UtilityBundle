<?php

namespace NyroDev\UtilityBundle\Model;

interface Sharable
{
    public function getMetaTitle();

    public function getOgTitle();

    public function getMetaDescription();

    public function getOgDescription();

    public function getMetaKeywords();

    public function getShareOgImage();

    public function getShareOthers();

    public function __toString();
}
