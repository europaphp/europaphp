<?php

namespace Europa\Controller\Filter;
use Europa\Exception\Exception;
use Europa\Controller\ControllerAbstract;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;

class TypeHint
{
    public function __invoke(ControllerAbstract $controller, ClassReflector $class, MethodReflector $method, array &$context)
    {
        foreach ($method->getParameters() as $param) {
            if (!$type = $param->getClass()) {
                continue;
            }

            $type = $type->getName();
            $name = $param->getName();

            if (isset($context[$name])) {
                $value = $context[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $value = $param->getDefaultValue();
            } else {
                Exception::toss(
                    'Cannot type-hint "%s" in "%s" because the request does not contain the parameter and a default value was not specified.',
                    $name,
                    $method->__toString()
                );
            }

            $context[$name] = new $type($value);
        }
    }
}