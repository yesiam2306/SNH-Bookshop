<?php

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/public', __DIR__]) // aggiungi cartelle se serve
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true, // base PSR-12
        // --- GRAFFE A CAPO (stile Allman) ---
        'curly_braces_position' => [
            'classes_opening_brace'            => 'next_line_unless_newline_at_signature_end',
            'anonymous_classes_opening_brace'  => 'next_line_unless_newline_at_signature_end',
            'functions_opening_brace'          => 'next_line_unless_newline_at_signature_end',
            'anonymous_functions_opening_brace'=> 'next_line_unless_newline_at_signature_end',
            'control_structures_opening_brace' => 'next_line', // if/for/while/… su riga nuova
            'allow_single_line_empty_body'     => false,
        ],
        // Posizione di else/elseif/catch/finally rispetto alla graffa che chiude
        'control_structure_continuation_position' => ['position' => 'next_line'],
    ])
    ->setFinder($finder);
