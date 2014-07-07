<?php
namespace NyroDev\UtilityBundle\Form\Type;

use Symfony\Component\Form\AbstractType as SrcAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\QueryBuilder;

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
		$builder
			->add('transformer', 'choice', array(
				'choices'=>array(
					'LIKE %...%'=>'LIKE %...%',
					'='=>'=',
				),
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
		
		return $queryBuilder;
    }
	
	public function applyValue($value) {
		return $value;
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