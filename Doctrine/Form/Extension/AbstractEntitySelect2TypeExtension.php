<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\FormExtensions\Doctrine\Form\Extension;

use Fxp\Component\FormExtensions\Doctrine\Form\ChoiceList\AjaxEntityLoaderInterface;
use Fxp\Component\FormExtensions\Doctrine\Form\ChoiceList\AjaxORMFilter;
use Fxp\Component\FormExtensions\Doctrine\Form\ChoiceList\AjaxORMQueryBuilderLoader;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractEntitySelect2TypeExtension extends DoctrineSelect2TypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getLoader(Options $options, $queryBuilder)
    {
        $qbTransformer = isset($options['query_builder_transformer']) ? $options['query_builder_transformer'] : null;

        return null !== $options['ajax_entity_loader']
            ? $options['ajax_entity_loader']
            : new AjaxORMQueryBuilderLoader($queryBuilder, $options['ajax_entity_filter'], $qbTransformer);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilderPartsForCachingHash($queryBuilder)
    {
        return [
            $queryBuilder->getQuery()->getSQL(),
            $queryBuilder->getParameters()->toArray(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'ajax_entity_loader' => null,
            'ajax_entity_filter' => null,
        ]);

        $resolver->addAllowedTypes('ajax_entity_loader', ['null', AjaxEntityLoaderInterface::class]);
        $resolver->addAllowedTypes('ajax_entity_filter', ['null', AjaxORMFilter::class]);

        parent::configureOptions($resolver);
    }
}
