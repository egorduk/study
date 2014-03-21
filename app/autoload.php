<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

/*$loader->registerNamespaces(array(
    'Zend' => __DIR__ . '/../vendor/zendframework/',
));*/

/*use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder->in('data/');*/

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
