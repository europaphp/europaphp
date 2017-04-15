<?php

namespace Europa\Di\Exception;

class CircularReference extends \RuntimeException
{
    public function __construct($name, array $references)
    {
        parent::__construct(sprintf('The service "%s" is being circularly referenced by: "%s".', $name, implode(' > ', $references)));
    }
}