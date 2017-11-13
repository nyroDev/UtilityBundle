<?php

namespace NyroDev\UtilityBundle\Utility;

use Symfony\Component\Process\Process;

class UnstopProcess extends Process
{
    public function __destruct()
    {
    }
}
