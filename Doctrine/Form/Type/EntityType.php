<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\FormExtensions\Doctrine\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Fxp\Component\FormExtensions\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Fxp\Component\FormExtensions\Doctrine\Form\ChoiceList\QueryBuilderTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType as BaseEntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class EntityType extends BaseEntityType
{
    /**
     * @var ORMQueryBuilderLoader|null
     */
    private $builderLoader;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if ($this->builderLoader instanceof ORMQueryBuilderLoader && $options['query_builder_transformer']) {
            $this->builderLoader->setQueryBuilderTransformer($options['query_builder_transformer']);
            $this->builderLoader = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class)
    {
        return $this->builderLoader = new ORMQueryBuilderLoader($queryBuilder);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'query_builder_transformer' => null,
        ]);

        $resolver->addAllowedTypes('query_builder_transformer', ['null', QueryBuilderTransformer::class]);
    }
}
