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
class FilterDbRowType extends FilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['showTransformer']) {
            $builder
            ->add('transformer', ChoiceType::class, array_merge([
                'choices' => [
                    '=' => AbstractQueryBuilder::OPERATOR_EQUALS,
                ],
            ], $options['transformerOptions']))
            ;
        }

        $nyrodevDb = $this->get(DbAbstractService::class);
        $myOptions = [
            'required' => false,
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
        return AbstractQueryBuilder::OPERATOR_EQUALS;
    }

    public function applyValue(mixed $value): mixed
    {
        return $value->getId();
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
        return 'filter_dbRow';
    }

    public function getParent(): ?string
    {
        return FilterType::class;
    }
}
