<?php

namespace NyroDev\UtilityBundle\Model;

interface Sharable
{
    public function getMetaTitle(): ?string;

    public function getOgTitle(): ?string;

    public function getMetaDescription(): ?string;

    public function getOgDescription(): ?string;

    public function getMetaKeywords(): ?string;

    public function getShareOgImage(): ?string;

    public function getShareOthers(): ?array;

    public function __toString();
}
