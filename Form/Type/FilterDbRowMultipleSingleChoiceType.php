<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectRepository;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use NyroDev\UtilityBundle\Services\Db\DbAbstractService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filter Type for Integer fields.
 */
class FilterDbRowMultipleSingleChoiceType extends FilterDbRowType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $nyrodevDb = $this->get(DbAbstractService::class);

        $myOptions = [
            'required' => false,
            'multiple' => false,
            'class' => $options['class'],
            'query_builder' => isset($options['query_builder']) || isset($options['where']) || isset($options['order']) ? function (ObjectRepository $or) use ($options,$nyrodevDb) {
                if (isset($options['query_builder'])) {
                    return $options['query_builder']($or);
                }

                $ret = $nyrodevDb->getQueryBuilder($or);
                /* @var $ret \NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder */

                if (isset($options['where']) && is_array($options['where'])) {
                    foreach ($options['where'] as $k => $v) {
                        if (is_int($k)) {
                            if (is_array($v)) {
                                $ret->addWhere($v[0], $v[1], isset($v[2]) ? $v[2] : null);
                            } else {
                                throw new \RuntimeException('Direct where setting is not supported anymore.');
                            }
                        } elseif (is_array($v)) {
                            $ret->addWhere($k, AbstractQueryBuilder::OPERATOR_IN, $v);
                        } else {
                            $ret->addWhere($k, AbstractQueryBuilder::OPERATOR_EQUALS, $v);
                        }
                    }
                }
                if (isset($options['order'])) {
                    $ret->orderBy($options['order'], 'ASC');
                }

                return $ret->getQueryBuilder();
            }
        : null,
        ];
        if (isset($options['property'])) {
            $myOptions['choice_label'] = $options['property'];
        }
        $builder
            ->add('transformer', ChoiceType::class, array_merge([
                'choices' => [
                    '=' => AbstractQueryBuilder::OPERATOR_IN,
                ],
            ], $options['transformerOptions']))
            ->add('value', $this->get(DbAbstractService::class)->getFormType(), array_merge($myOptions, $options['valueOptions']));
    }

    public function applyFilter(AbstractQueryBuilder $queryBuilder, $name, $data)
    {
        if (
                isset($data['transformer']) && $data['transformer']
            && isset($data['value']) && $data['value']
            ) {
            $value = $this->applyValue($data['value']);

            if ($value) {
                $queryBuilder->addJoinWhere($name, [$value]);
            }
        }

        return $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => null,
            'property' => null,
            'query_builder' => null,
            'where' => null,
            'order' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'filter_dbRowMultipleSingleChoice';
    }

    public function getParent()
    {
        return FilterType::class;
    }
}
