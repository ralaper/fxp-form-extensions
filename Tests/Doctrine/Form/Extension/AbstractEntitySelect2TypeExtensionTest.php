<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\FormExtensions\Tests\Doctrine\Form\Extension;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;
use Fxp\Component\FormExtensions\Doctrine\Form\ChoiceList\AjaxEntityLoaderInterface;
use Fxp\Component\FormExtensions\Doctrine\Form\Loader\AjaxDoctrineChoiceLoader;
use Fxp\Component\FormExtensions\Form\ChoiceList\Formatter\Select2AjaxChoiceListFormatter;
use Fxp\Component\FormExtensions\Form\Extension\ChoiceSelect2TypeExtension;
use Fxp\Component\FormExtensions\Tests\Form\Extension\AbstractSelect2TypeExtensionTest;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Bridge\Doctrine\Tests\Fixtures\SingleIntIdEntity;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormFactoryBuilderInterface;

/**
 * Tests case for entity of select2 form extension type.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractEntitySelect2TypeExtensionTest extends AbstractSelect2TypeExtensionTest
{
    const SINGLE_INT_ID_CLASS = 'Symfony\Bridge\Doctrine\Tests\Fixtures\SingleIntIdEntity';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $emRegistry;

    /**
     * @var array
     */
    protected $items;

    protected function setUp()
    {
        if (!class_exists('Symfony\Component\Form\Form')) {
            $this->markTestSkipped('The "Form" component is not available');
        }

        if (!class_exists('Doctrine\DBAL\Platforms\MySqlPlatform')) {
            $this->markTestSkipped('Doctrine DBAL is not available.');
        }

        if (!class_exists('Doctrine\Common\Persistence\PersistentObject')) {
            $this->markTestSkipped('Doctrine Common Persistence is not available.');
        }

        if (!class_exists('Doctrine\ORM\EntityManager')) {
            $this->markTestSkipped('Doctrine ORM is not available.');
        }

        $this->em = DoctrineTestHelper::createTestEntityManager();
        $this->emRegistry = $this->createRegistryMock('default', $this->em);
        $this->items = [];

        $schemaTool = new SchemaTool($this->em);
        $classes = [
            $this->em->getClassMetadata(self::SINGLE_INT_ID_CLASS),
        ];

        try {
            $schemaTool->dropSchema($classes);
        } catch (\Exception $e) {
        }

        try {
            $schemaTool->createSchema($classes);
        } catch (\Exception $e) {
        }

        $this->createEntities();

        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->em = null;
        $this->items = null;
    }

    protected function buildFormFactory(FormFactoryBuilderInterface $factoryBuilder)
    {
        $typeName = $this->getTypeName();
        $extName = $this->getExtensionTypeName();
        $factoryBuilder
            ->addTypeExtension(new ChoiceSelect2TypeExtension($this->dispatcher, $this->requestStack, $this->router, 10))
            ->addType(new $typeName($this->emRegistry))
            ->addTypeExtension(new $extName())
        ;
    }

    protected function createRegistryMock($name, $em)
    {
        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')->getMock();
        $registry->expects($this->any())
            ->method('getManager')
            ->with($this->equalTo($name))
            ->will($this->returnValue($em))
        ;
        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->with($this->equalTo($this::SINGLE_INT_ID_CLASS))
            ->will($this->returnValue($em))
        ;

        return $registry;
    }

    protected function createEntities()
    {
        $item1 = new SingleIntIdEntity(1, 'Foo');
        $item2 = new SingleIntIdEntity(2, 'Bar');
        $item3 = new SingleIntIdEntity(3, 'Baz');
        $item4 = new SingleIntIdEntity(4, 'Boo!');

        $this->items = [1 => $item1, 2 => $item2, 3 => $item3, 4 => $item4];

        $this->em->persist($item1);
        $this->em->persist($item2);
        $this->em->persist($item3);
        $this->em->persist($item4);
        $this->em->flush();
    }

    protected function mergeOptions(array $options)
    {
        $options = parent::mergeOptions($options);
        $options['class'] = $this::SINGLE_INT_ID_CLASS;

        return $options;
    }

    protected function getSingleData()
    {
        return $this->items[1];
    }

    protected function getValidSingleValue()
    {
        return '1';
    }

    protected function getValidAjaxSingleValue()
    {
        return '1';
    }

    protected function getMultipleData()
    {
        return [$this->items[1], $this->items[2]];
    }

    protected function getValidMultipleValue()
    {
        return ['1', '2'];
    }

    protected function getValidAjaxMultipleValue()
    {
        return $this->getValidMultipleValue();
    }

    protected function getValidFirstChoiceSelected()
    {
        $formatter = new Select2AjaxChoiceListFormatter();
        $choice = new ChoiceView($this->items[1], '1', 'Foo');

        return $formatter->formatChoice($choice);
    }

    protected function validateChoiceLoaderForDefaultOptions(FormConfigInterface $config)
    {
        $this->assertInstanceOf('Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader', $config->getOption('choice_loader'));
    }

    public function testInvalidChoiceLoaderOption()
    {
        // Skip test
        $this->assertTrue(true);
    }

    public function testWithQueryBuilder()
    {
        $qb = $this->em->createQueryBuilder()
            ->select('e')
            ->from($this::SINGLE_INT_ID_CLASS, 'e')
        ;
        $options = [
            'query_builder' => $qb,
            'select2' => [
                'enabled' => true,
            ],
        ];

        $form = $this->factory->create($this->getTypeName(), $this->getSingleData(), $this->mergeOptions($options));
        $config = $form->getConfig();

        $this->assertFalse($config->getOption('compound'));
        $this->assertFalse($config->getOption('multiple'));
        $this->assertTrue($config->hasOption('select2'));
        $select2Opts = $config->getOption('select2');
        $this->assertTrue($select2Opts['enabled']);
        $this->assertFalse($select2Opts['ajax']);
        $this->assertFalse($select2Opts['tags']);
        $this->assertInstanceOf('Fxp\Component\FormExtensions\Form\ChoiceList\Loader\DynamicChoiceLoaderInterface', $config->getOption('choice_loader'));

        // test cache with hash
        $this->factory->create($this->getTypeName(), $this->getSingleData(), $this->mergeOptions($options));
    }

    public function testAjaxEntityLoaderOption()
    {
        $ael = $this->getMockBuilder(AjaxEntityLoaderInterface::class)->getMock();
        $options = [
            'ajax_entity_loader' => $ael,
            'select2' => [
                'enabled' => true,
                'ajax' => true,
            ],
        ];

        $form = $this->factory->create($this->getTypeName(), $this->getSingleData(), $this->mergeOptions($options));
        $config = $form->getConfig();
        $choiceLoader = $config->getOption('choice_loader');

        $this->assertInstanceOf(AjaxDoctrineChoiceLoader::class, $choiceLoader);

        $ref = new \ReflectionClass($choiceLoader);
        $prop = $ref->getProperty('objectLoader');
        $prop->setAccessible(true);
        $objectLoader = $prop->getValue($choiceLoader);

        $this->assertSame($ael, $objectLoader);
    }

    public function testAjaxRouteAttribute()
    {
        // Skip test
        $this->assertTrue(true);
    }

    public function testGetExtendedType()
    {
        $extName = $this->getExtensionTypeName();
        $ext = new $extName();
        $this->assertEquals($extName::getExtendedTypes(), [$ext->getExtendedType()]);
    }
}
