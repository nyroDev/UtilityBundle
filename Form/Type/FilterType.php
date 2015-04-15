<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\AbstractType as SrcAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Default Filter Type field for text fields 
 */
class FilterType extends SrcAbstractType implements FilterTypeInterface {
	
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
	
    public function applyFilter(QueryBuilder $queryBuilder, $name, $data) {
		if (
				isset($data['transformer']) && $data['transformer']
			&&  isset($data['value']) && $data['value']
			) {
			$value = $this->applyValue($data['value']);
			$transformer = $data['transformer'];
			
			if ($transformer == 'IS NULL' || $transformer == 'IS NOT NULL') {
				$condMask = '%s.%s %s';
				$condition = sprintf($condMask,
					$queryBuilder->getRootAlias(),
					$name,
					$transformer
				);

				$queryBuilder->andWhere($condition);
			} else {
				$paramName = $name.'_param';
				$condMask = '%s.%s %s :%s';

				if ($transformer == 'LIKE %...%') {
					$value = '%'.$value.'%';
					$transformer = 'LIKE';
				}
				$condition = sprintf($condMask,
					$queryBuilder->getRootAlias(),
					$name,
					$transformer,
					$paramName
				);

				$queryBuilder->andWhere($condition)->setParameter($paramName, $value, \PDO::PARAM_STR);
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