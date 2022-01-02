<?php
// content="text/plain; charset=utf-8"
// Antispam example using a random string
require_once __DIR__ . '/jpgraph/jpgraph_antispam.php';

// Create new anti-spam challenge creator
// Note: Neither '0' (digit) or 'O' (letter) can be used to avoid confusion
$spam = new AntiSpam();

// Create a random 5 char challenge and return the string generated
$chars = $spam->Rand(5);

// Stroke random cahllenge
if (false === $spam->Stroke()) {
    exit('Illegal or no data to plot');
}

?>

