<?php

require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_windrose.php';

// Data can be specified using both ordinal index of the axis
// as well as the direction label
$data = [
    0     => [5, 5, 5, 8],
    1     => [3, 4, 1, 4],
    'WSW' => [1, 5, 5, 3],
    'N'   => [2, 3, 8, 1, 1],
    15    => [2, 3, 5],
];

// First create a new windrose graph with a title
$graph = new WindroseGraph(400, 400);
$graph->title->Set('A basic Windrose graph');

// Create the windrose plot.
$wp = new WindrosePlot($data);

// Add and send back to browser
$graph->Add($wp);
$graph->Stroke();
?>

