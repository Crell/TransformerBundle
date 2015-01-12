<?php

/**
 * This file is part of the Transformer Bundle library.
 *
 * (c) Larry Garfield <larry@garfieldtech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Crell\Transformer
 */

namespace Crell\TransformerBundle\Tests;

use Crell\Transformer\Tests\MethodConverterTest;
use Crell\Transformer\Tests\TestA;
use Crell\Transformer\Tests\TestB;
use Crell\Transformer\Tests\TransformerBusTest;
use Crell\TransformerBundle\ContainerAwareTransformerBus;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContainerAwareTransformerBusTest extends TransformerBusTest
{
    protected $classToTest = 'Crell\TransformerBundle\ContainerAwareTransformerBus';

    /**
     * {@inheritdoc}
     */
    protected function createTransformerBus($target)
    {
        $container = $this->createMockContainer();

        $container->expects($this->any())
          ->method('has')
          ->with('foo')
          ->will($this->returnValue(true))
        ;
        $container->expects($this->any())
          ->method('get')
          ->with('foo')
          ->will($this->returnValue(new ServiceTransformer()))
        ;

        $bus = new ContainerAwareTransformerBus($target);
        $bus->setContainer($container);
        return $bus;
    }

    protected function createMockContainer()
    {
        return $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
    }

    /**
     * {@inheritdoc}
     */
    public function transformerDefinitionProvider()
    {
        $defs = parent::transformerDefinitionProvider();

        // Successful transformations.
        $defs[] = [TestA::CLASSNAME, TestB::CLASSNAME, MethodConverterTest::CLASSNAME . '::transform'];
        $defs[] = [TestA::CLASSNAME, TestB::CLASSNAME, 'foo:transform'];

        // Transformations that should fail.
        // @todo I am unclear how to make this work without a custom test method and custom container.
        //$defs[] = [TestA::CLASSNAME, TestB::CLASSNAME, 'no_such_service:transform', 'Crell\Transformer\NoTransformerFoundException'];

        return $defs;
    }
}

class ServiceTransformer
{
    public function transform(TestA $a)
    {
        return new TestB();
    }
}
