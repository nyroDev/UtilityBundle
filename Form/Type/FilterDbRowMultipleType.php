<?php

namespace NyroDev\UtilityBundle\Form\Type;

use Doctrine\Persistence\ObjectRepository;
use NyroDev\UtilityBundle\QueryBuilder\AbstractQueryBuilder;
use NyroDev\UtilityBundle\Services\Db\DbAbstractService;
use RuntimeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filter Type for Integer fields.
 */
class FilterDbRowMultipleType extends FilterDbRowType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['showTransformer']) {
            $builder
                ->add('transformer', ChoiceType::class, array_merge([
                    'choices' => [
                        'IN' => AbstractQueryBuilder::OPERATOR_IN,
                    ],
                ], $options['transformerOptions']))
            ;
        }
        $nyrodevDb = $this->get(DbAbstractService::class);
        $myOptions = [
            'required' => false,
            'multiple' => true,
            'attr' => [
                'class' => 'multiple',
            ],
            'class' => $options['class'],
            'query_builder' => isset($options['query_builder']) || isset($options['where']) || isset($options['order']) ? function (ObjectRepository $or) use ($options, $nyrodevDb) {
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
                                throw new RuntimeException('Direct where setting is not supported anymore.');
                            }
                        } elseif (is_array($v)) {
                            $ret->addWhere($k, AbstractQueryBuilder::OPERATOR_IN, $v);
                        } else {
                            $ret->addWhere($k, AbstractQueryBuilder::OPERATOR_EQUALS, $v);
                        }
                    }
                }
                if (isset($options['order'])) {
                    $ret->orderBy($options['order'], $options['orderDir']);
                }

                return $ret->getQueryBuilder();
            }
        : null,
        ];
        if (isset($options['property'])) {
            $myOptions['choice_label'] = $options['property'];
        }
        $builder->add('value', $this->get(DbAbstractService::class)->getFormType(), array_merge($myOptions, $options['valueOptions']));
    }

    public function getDefaultTransformer(): string
    {
        return AbstractQueryBuilder::OPERATOR_IN;
    }

    public function applyFilter(AbstractQueryBuilder $queryBuilder, string $name, array $data): AbstractQueryBuilder
    {
        if (isset($data['value']) && $data['value']) {
            $value = $this->applyValue($data['value']);
            $transformer = isset($data['transformer']) && $data['transformer'] ? $data['transformer'] : $this->getDefaultTransformer();

            if (count($value) > 0) {
                $queryBuilder->addJoinWhere($name, $value);
            }
        }

        return $queryBuilder;
    }

    public function applyValue(mixed $value): array
    {
        $ret = [];
        foreach ($value as $val) {
            $ret[] = $val->getId();
        }

        return array_filter($ret);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => null,
            'property' => null,
            'query_builder' => null,
            'where' => null,
            'order' => null,
            'orderDir' => 'ASC',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'filter_dbRowMultiple';
    }

    public function getParent(): ?string
    {
        return FilterType::class;
    }
}
