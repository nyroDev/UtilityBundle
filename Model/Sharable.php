<?php

namespace NyroDev\UtilityBundle\Model;

interface Sharable
{
    public function getMetaTitle();

    public function getOgTitle();

    public function getMetaDescription();

    public function getOgDescription();

    public function getMetaKeywords();

    public function getOgImageFile();

    public function getShareOthers();

    public function __toString();
}
