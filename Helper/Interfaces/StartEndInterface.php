<?php

namespace NyroDev\UtilityBundle\Helper\Interfaces;

use DateTimeInterface;

interface StartEndInterface
{
    public function getStartDate(): ?DateTimeInterface;

    public function getEndDate(): ?DateTimeInterface;
}
