<?php

namespace Europa\Reflection;
use Europa\Exception\Exception;

trait ParameterAwareTrait
{
    public function mergeNamedArgs(array $params, $caseSensitive = false)
    {
        $merged = [];

        foreach ($params as $name => $value) {
            if (is_numeric($name)) {
                $merged[(int) $name] = $value;
            } elseif (!$caseSensitive) {
                $params[strtolower($name)] = $value;
            }
        }

        foreach ($this->getParameters() as $param) {
            $pos  = $param->getPosition();
            $name = $caseSensitive ? $param->getName() : strtolower($param->getName());

            if (array_key_exists($name, $params)) {
                $merged[$pos] = $params[$name];
            } elseif (array_key_exists($pos, $params)) {
                $merged[$pos] = $params[$pos];
            } elseif ($param->isOptional()) {
                $merged[$pos] = $param->getDefaultValue();
            } else {
                Exception::toss('The required parameter "%s" for "%s()" was not specified.', $param->getName(), $this->__toString());
            }
        }

        return $merged;
    }
}