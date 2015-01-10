<?php

namespace Crell\TransformerBundle\Tests;

use Crell\Transformer\Tests\MethodConverterTest;
use Crell\Transformer\Tests\TestA;
use Crell\Transformer\Tests\TestB;
use Crell\Transformer\Tests\TransformerBusTest;
use Crell\TransformerBundle\ContainerAwareTransformerBus;
use Symfony\Component\DependencyInjection\Container;

class ContainerAwareTransformerBusTest extends TransformerBusTest
{
    protected $classToTest = 'Crell\TransformerBundle\ContainerAwareTransformerBus';

    protected function createTransformerBus($target) {
        $container = new Container();
        $bus = new ContainerAwareTransformerBus($target);
        $bus->setContainer($container);
        return $bus;
    }

    /**
     * Defines an array of transformers that convert from TestA to TestB.
     */
    public function transformerDefinitionProvider()
    {
        $defs = parent::transformerDefinitionProvider();

        $defs[] = [TestA::CLASSNAME, TestB::CLASSNAME, MethodConverterTest::CLASSNAME . '::transform'];

        return $defs;
    }
}
