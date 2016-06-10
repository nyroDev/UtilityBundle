<?php

namespace NyroDev\UtilityBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidConfig extends Constraint
{
    public $message = 'The config is not a valid json.';
}
