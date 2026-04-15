<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
;

return (new PhpCsFixer\Config())
    ->setRules([
        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline' => true,
        '@Symfony' => true,
        'increment_style' => ['style' => 'post'],
        'concat_space' => ['spacing' => 'one'],
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false
        ],
        'multiline_promoted_properties' => [
            'keep_blank_lines' => false,
            'minimum_number_of_parameters' => 1,
        ],
    ])
    ->setFinder($finder)
;
