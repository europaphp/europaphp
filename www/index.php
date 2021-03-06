<?php

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Europaphp\Main\Di\Container;
$container('loader')->register();
$container('modules')
  ->add(new Europaphp\Help)
  ->add(new Europaphp\Main);

$app = $container('app');
$app();