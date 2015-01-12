<?php

namespace Crell\TransformerBundle;

use Crell\Transformer\NoTransformerFoundException;
use Crell\Transformer\TransformerBus;
use Crell\Transformer\TransformerBusInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * A Symfony-specific Transformer Bus that allows Symfony extended callables as transformers.
 *
 * This allows transformers to be instantiated on-demand, and thus not
 * instantiated if they are never used.
 *
 * Most of the container-handling code in this class was copied directly from
 * FrameworkBundle\ControllerResolver. That code really ought to be factored
 * out to a separate class.
 */
class ContainerAwareTransformerBus extends TransformerBus implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * An associative array of transformers.
     *
     * The key is the class name the transformer will handle, and the value
     * is a Symfony "extended" PHP callable (which includes PHP callables as
     * well as the service:method and ClassToInstantaite::method formats) that
     * can convert objects of that class to something else.
     *
     * @var array
     */
    protected $transformers = [];

    /**
     * Constructs a new TransformerBus.
     *
     * @param string $targetClass
     *   The name of the class we are after.
     */
    public function __construct($targetClass)
    {
        parent::__construct($targetClass);
    }

    /**
     * Sets the transformer for a specified type.
     *
     * @param string $class
     *   The class this transformer can handle.
     * @param callable|string $transformer
     *   A Symfony callable that will transform an object of type $class to something else.
     */
    public function setTransformer($class, $transformer)
    {
        $this->transformers[$class] = $transformer;
    }

    /**
     * @{inheritdoc}
     */
    protected function getTransformer($class)
    {
        if (!isset($this->transformers[$class])) {
            throw new NoTransformerFoundException(sprintf("No transformer registered for class '%s'", $class));
        }
        return $this->getInstantiatedTransformer($this->transformers[$class]);
    }

    /**
     * Returns a callable for a given transformer.
     *
     * @param string $transformer
     *   A transformer string
     * @return mixed
     *   A PHP callable
     *
     * @throws \InvalidArgumentException
     */
    public function getInstantiatedTransformer($transformer)
    {
        if (is_array($transformer)) {
            return $transformer;
        }

        if (is_object($transformer)) {
            if (method_exists($transformer, '__invoke')) {
                return $transformer;
            }

            throw new \InvalidArgumentException(sprintf('Transformer "%s" is not callable.', get_class($transformer)));
        }

        if (false === strpos($transformer, ':')) {
            if (method_exists($transformer, '__invoke')) {
                return $this->instantiateTransformer($transformer);
            } elseif (function_exists($transformer)) {
                return $transformer;
            }
        }

        $callable = $this->createTransformer($transformer);

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException(sprintf('Transformer "%s" is not callable.', get_class($transformer)));
        }

        return $callable;
    }

    /**
     * Returns a callable for the given transformer.
     *
     * @param string $transformer A transformer string
     *
     * @return mixed A PHP callable
     *
     * @throws \LogicException
     *   When the name could not be parsed
     * @throws \InvalidArgumentException
     *   When the transformer class does not exist
     */
    protected function createTransformer($transformer)
    {
        if (false === strpos($transformer, '::')) {
            $count = substr_count($transformer, ':');
            if (1 == $count) {
                // transformer in the service:method notation
                list($service, $method) = explode(':', $transformer, 2);

                return array($this->container->get($service), $method);
            } elseif ($this->container->has($transformer) && method_exists($service = $this->container->get($transformer), '__invoke')) {
                return $service;
            } else {
                throw new \LogicException(sprintf('Unable to parse the transformer name "%s".', $transformer));
            }
        }

        list($class, $method) = explode('::', $transformer, 2);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $transformer = new $class();
        if ($transformer instanceof ContainerAwareInterface) {
            $transformer->setContainer($this->container);
        }

        return array($transformer, $method);
    }

    /**
     * Returns an instantiated transformer.
     *
     * @param string $class
     *   A class name
     *
     * @return object
     */
    protected function instantiateTransformer($class)
    {
        return new $class();
    }
}
