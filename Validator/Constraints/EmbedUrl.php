<?php

namespace NyroDev\UtilityBundle\Validator\Constraints;

use NyroDev\UtilityBundle\Services\EmbedValidatorService;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EmbedUrl extends Constraint
{
    public $type = 'video';
    public $allowNotFetched = false;
    public $allowNoUrlEmbed = false;
    public $messageNotFetched = 'The URL you provided cannot be fetched.';
    public $messageNoType = 'The URL you provided does not seem to contain a %type%.';
    public $messageNoEmbed = 'The URL you provided does not seem to contain embed informations.';

    public function validatedBy()
    {
        return EmbedValidatorService::class;
    }
}
