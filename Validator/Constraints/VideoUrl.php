<?php
namespace NyroDev\UtilityBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EmbedUrl extends Constraint {
	
	public $type = 'video';
	public $messageNotFetched = 'The URL you provided cannot be fetched.';
	public $messageNoEmbed = 'The URL you provided does not seem to contain a %type%.';
	public $messageNoEmbed = 'The URL you provided does not seem to contain embed informations.';
	
	public function validatedBy() {
		return 'nyrodev_embed_validator';
	}
	
}