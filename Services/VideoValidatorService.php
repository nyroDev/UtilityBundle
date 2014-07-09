<?php
namespace NyroDev\UtilityBundle\Services;

use Symfony\Component\Validator;


class VideoValidatorService extends AbstractService implements Validator\ConstraintValidatorInterface {

    /**
     * @var ExecutionContextInterface
     */
    protected $context;
	
	public function initialize(Validator\ExecutionContextInterface $context) {
		$this->context = $context;
	}

	public function validate($value, Validator\Constraint $constraint) {
		if ($value) {
			$urlValidator = new Validator\Constraints\UrlValidator();
			$urlValidator->initialize($this->context);
			$urlValidator->validate($value, new Validator\Constraints\Url());
			if (count($this->context->getViolations()) === 0) {
				$dataUrl = $this->get('nyrodev_video')->data($value);
				$error = null;
				if (!is_array($dataUrl)) {
					$error = 'NotFetched';
				} else if ($dataUrl['type'] != 'video') {
					$error = 'NoVideo';
				} else if (!isset($dataUrl['urlEmbed']) || !$dataUrl['urlEmbed']) {
					$error = 'NoEmbed';
				}
				if (!is_null($error)) {
					$errorMessage = 'message'.$error;
					$this->context->addViolation($constraint->$errorMessage);
				}
			}
		}
	}

}

