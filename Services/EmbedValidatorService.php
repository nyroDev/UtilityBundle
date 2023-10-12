<?php

namespace NyroDev\UtilityBundle\Services;

use Symfony\Component\Validator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EmbedValidatorService extends AbstractService implements Validator\ConstraintValidatorInterface
{
    /**
     * @var ExecutionContextInterface
     */
    protected $context;

    public function initialize(ExecutionContextInterface $context)
    {
        $this->context = $context;
    }

    public function validate($value, Validator\Constraint $constraint)
    {
        if ($value) {
            $urlValidator = new Validator\Constraints\UrlValidator();
            $urlValidator->initialize($this->context);
            $urlValidator->validate($value, new Validator\Constraints\Url());
            if (0 === count($this->context->getViolations())) {
                $dataUrl = $this->get(EmbedService::class)->data($value);
                $error = null;
                $prms = [];
                if (!is_array($dataUrl) || 0 === count($dataUrl)) {
                    if (!$constraint->allowNotFetched) {
                        $error = 'NotFetched';
                    }
                } elseif (
                    $constraint->type && (
                        (is_array($constraint->type) && !in_array($dataUrl['type'], $constraint->type))
                        ||
                        (!is_array($constraint->type) && $dataUrl['type'] != $constraint->type)
                    )
                ) {
                    $error = 'NoType';
                    $prms = ['%type%' => is_array($constraint->type) ? implode(', ', $constraint->type) : $constraint->type];
                } elseif (!$constraint->allowNoUrlEmbed && (!isset($dataUrl['urlEmbed']) || !$dataUrl['urlEmbed'])) {
                    $error = 'NoEmbed';
                }
                if (!is_null($error)) {
                    $errorMessage = 'message'.$error;
                    $this->context->addViolation($constraint->$errorMessage, $prms);
                }
            }
        }
    }
}
