<?php

namespace NyroDev\UtilityBundle\Validator\Constraints;

use NyroDev\UtilityBundle\Services\EmbedValidatorService;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EmbedUrl extends Constraint
{
    public string $type = 'video';
    public bool $allowNotFetched = false;
    public bool $allowNoUrlEmbed = false;
    public string $messageNotFetched = 'The URL you provided cannot be fetched.';
    public string $messageNoType = 'The URL you provided does not seem to contain a %type%.';
    public string $messageNoEmbed = 'The URL you provided does not seem to contain embed informations.';

    public function validatedBy(): string
    {
        return EmbedValidatorService::class;
    }
}
