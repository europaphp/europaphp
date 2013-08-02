<?php

namespace Europa\Filter;

class UpperCamelCaseFilter
{
  public function __invoke($value)
  {
    $temp  = array();
    $parts = preg_split('/[^a-zA-Z0-9]/', $value);

    foreach ($parts as $part) {
      $part = trim($part);

      if (!$part) {
        continue;
      }

      $temp[] = ucfirst($part);
    }

    $value = implode('', $temp);

    return $value;
  }
}