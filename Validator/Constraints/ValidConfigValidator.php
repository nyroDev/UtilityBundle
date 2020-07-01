<?php

namespace NyroDev\UtilityBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class ValidConfigValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (false === $value) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
