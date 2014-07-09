<?php
namespace NyroDev\UtilityBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class VideoUrl extends Constraint {
	
	public $messageNotFetched = 'The URL you provided cannot be fetched.';
	public $messageNoVideo = 'The URL you provided does not seem to contain a video.';
	public $messageNoEmbed = 'The URL you provided does not seem to contain embed informations.';
	
	public function validatedBy() {
		return 'nyrodev_video_validator';
	}
	
}