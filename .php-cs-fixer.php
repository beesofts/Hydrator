<?php

$finder = PhpCsFixer\Finder::create()
                           ->in([
                               __DIR__ . '/src',
                               __DIR__ . '/tests',
                           ])
;

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    '@Symfony' => true,
    'strict_param' => true,
    'concat_space' => false,
    'operator_linebreak' => false,
    'phpdoc_align' => false,
    'array_syntax' => ['syntax' => 'short'],
])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
