<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\FormExtensions\Form\Extension;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Util\StringUtil;

/**
 * Base of choice type extension for types that have choice type for parent type.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractChoiceSelect2TypeExtension extends AbstractSelect2TypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (null === $options['select2']['ajax_route']) {
            $builder->setAttribute('select2_ajax_route', 'fxp_form_extensions_ajax_'.StringUtil::fqcnToBlockPrefix(current(static::getExtendedTypes())));
        }
    }
}
