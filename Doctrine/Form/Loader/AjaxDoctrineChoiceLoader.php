<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\FormExtensions\Doctrine\Form\Loader;

use Fxp\Component\FormExtensions\Doctrine\Form\ChoiceList\AjaxEntityLoaderInterface;
use Fxp\Component\FormExtensions\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface;
use Fxp\Component\FormExtensions\Form\ChoiceList\Loader\Traits\AjaxLoaderTrait;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AjaxDoctrineChoiceLoader extends DynamicDoctrineChoiceLoader implements AjaxChoiceLoaderInterface
{
    use AjaxLoaderTrait;

    /**
     * @var AjaxEntityLoaderInterface
     */
    protected $objectLoader;

    /**
     * Creates a new choice loader.
     *
     * @param AjaxEntityLoaderInterface         $objectLoader The objects loader
     * @param callable                          $choiceValue  The callable choice value
     * @param string                            $idField      The id field
     * @param null|callable|string|PropertyPath $label        The callable or path generating the choice labels
     * @param ChoiceListFactoryInterface|null   $factory      The factory for creating
     *                                                        the loaded choice list
     */
    public function __construct(AjaxEntityLoaderInterface $objectLoader, $choiceValue, $idField, $label, $factory = null)
    {
        parent::__construct($objectLoader, $choiceValue, $idField, $label, $factory);

        $this->initAjax();
        $this->reset();
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->objectLoader->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function loadPaginatedChoiceList($value = null)
    {
        $objects = $this->objectLoader->getPaginatedEntities($this->getPageSize(), $this->getPageNumber());
        $value = $this->getCallableValue($value);

        return $this->factory->createListFromChoices($objects, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoiceListForView(array $values, $value = null)
    {
        return $this->factory->createListFromChoices($values, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->objectLoader->reset();
        $this->objectLoader->setSearch((string) $this->label, $this->getSearch());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadEntities()
    {
        return [];
    }
}
