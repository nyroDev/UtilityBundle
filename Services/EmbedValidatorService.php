<?php
namespace NyroDev\UtilityBundle\Services;

use Symfony\Component\Validator;

class EmbedValidatorService extends AbstractService implements Validator\ConstraintValidatorInterface {

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
				$dataUrl = $this->get('nyrodev_embed')->data($value);
				$error = null;
				$prms = array();
				if (!is_array($dataUrl) || count($dataUrl) === 0) {
					$error = 'NotFetched';
				} else if ($dataUrl['type'] != $constraint->type) {
					$error = 'NoType';
					$prms = array('%type%'=>$constraint->type);
				} else if (!isset($dataUrl['urlEmbed']) || !$dataUrl['urlEmbed']) {
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

