<?php

require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_windrose.php';

$data = [
    0     => [1, 1, 2.5, 4],
    1     => [3, 4, 1, 4],
    'wsw' => [1, 5, 5, 3],
    'N'   => [2, 7, 5, 4, 2],
    15    => [2, 7, 12],
];

// First create a new windrose graph with a title
$graph = new WindroseGraph(400, 400);

// Setup title
$graph->title->Set('Windrose basic example');
$graph->title->SetFont(FF_VERDANA, FS_BOLD, 12);
$graph->title->SetColor('navy');

// Create the windrose plot.
$wp = new WindrosePlot($data);
$wp->SetRadialGridStyle('solid');
$graph->Add($wp);

// Send the graph to the browser
$graph->Stroke();
?>

