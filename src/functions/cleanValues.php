<?php
function cleanValue($valueToClean) {
    $cleanedValue = iconv('UTF-8', 'ASCII//TRANSLIT', $valueToClean); // Translittération
    // var_dump($cleanedValue); // Debug
    $cleanedValue = preg_replace("/'/", '', $cleanedValue); // Supprimer les apostrophes
    // var_dump($cleanedValue); // Debug
    $cleanedValue = preg_replace('/`/', '', $cleanedValue); // Quotes
    // var_dump($cleanedValue); // Debug
    $cleanedValue = preg_replace('/"/', '', $cleanedValue); // Guillemets
    // var_dump($cleanedValue); // Debug
    $cleanedValue = preg_replace("/\^/", '', $cleanedValue); // Circonflexe
    // var_dump($cleanedValue); // Debug
    $cleanedValue = strtoupper($cleanedValue); // Majuscules
    // var_dump($cleanedValue); // Debug
    $cleanedValue = preg_replace('/[^A-Za-z0-9\-]+/', '_', $cleanedValue); // Garder uniquement alpha numérique et tirets
    // var_dump($cleanedValue); // Debug
    $cleanedValue = preg_replace('/_+/', '_', $cleanedValue); // Supprimer les doubles underscores

    return $cleanedValue;
}
