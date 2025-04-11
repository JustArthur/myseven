<?php
function cleanValue($valueToClean) {
    $cleanedValue = iconv('UTF-8', 'ASCII//TRANSLIT', $valueToClean); // Translittération
    $cleanedValue = preg_replace("/'/", '', $cleanedValue); // Supprimer les apostrophes
    $cleanedValue = preg_replace('/`/', '', $cleanedValue); // Quotes
    $cleanedValue = preg_replace('/"/', '', $cleanedValue); // Guillemets
    $cleanedValue = preg_replace("/\^/", '', $cleanedValue); // Circonflexe
    $cleanedValue = strtoupper($cleanedValue); // Majuscules
    $cleanedValue = preg_replace('/[^A-Za-z0-9\-]+/', '_', $cleanedValue); // Garder uniquement alpha numérique et tirets
    $cleanedValue = preg_replace('/_+/', '_', $cleanedValue); // Supprimer les doubles underscores
    return $cleanedValue;
}
