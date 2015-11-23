<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Default Filter Type field for text fields 
 */
class FilterType extends AbstractType implements FilterTypeInterface {
	
	/**
	 * Builds the form.
	 *
	 * @see FormTypeExtensionInterface::buildForm()
	 * @param FormBuilderInterface   $builder The form builder
	 * @param array         $options The options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$choices = array(
			'LIKE %...%'=>'LIKE %...%',
			'='=>'=',
		);
		if ($options['addNullTransformer']) {
			$choices['IS NULL'] = 'IS NULL';
			$choices['IS NOT NULL'] = 'IS NOT NULL';
		}
		$builder
			->add('transformer', 'choice', array(
				'choices'=>$choices,
			))
			->add('value', 'text', array(
				'required'=>false,
			));
	}
	
    public function applyFilter(AbstractQueryBuilder $queryBuilder, $name, $data) {
		if (
				isset($data['transformer']) && $data['transformer']
			&&  isset($data['value']) && $data['value']
			) {
			$value = $this->applyValue($data['value']);
			$transformer = $data['transformer'];
			
			if ($transformer == 'IS NULL' || $transformer == 'IS NOT NULL') {
				$queryBuilder->addWhere($name, $transformer == 'IS NULL' ? AbstractQueryBuilder::WHERE_IS_NULL : AbstractQueryBuilder::WHERE_IS_NOT_NULL);
			} else {
				if ($transformer == 'LIKE %...%') {
					$value = '%'.$value.'%';
					$transformer = 'LIKE';
				}
				$queryBuilder->addWhere($name, $transformer, $value, \PDO::PARAM_STR);
			}
		}
		
		return $queryBuilder;
    }
	
	public function applyValue($value) {
		return $value;
	}
	
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'addNullTransformer'=>false,
		));
    }
	
	/**
	 * Returns the name of this type.
	 *
	 * @return string The name of this type
	 */
	public function getName() {
		return 'filter';
	}
	
	/**
	 * Returns the name of the parent type.
	 *
	 * @return string|null The name of the parent type if any otherwise null
	 */
	public function getParent() {
		return 'form';
	}

}