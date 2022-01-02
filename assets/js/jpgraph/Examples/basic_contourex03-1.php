<?php
// content="text/plain; charset=utf-8"
// Basic contour plot example

require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_contour.php';

$data = [
    [12, 7, 3, 15],
    [18, 5, 1, 9],
    [13, 9, 5, 12],
    [5, 3, 8, 9],
    [1, 8, 5, 7],
];

// Basic contour graph
$graph = new Graph(350, 250);
$graph->setScale('intint');

// Show axis on all sides
$graph->setAxisStyle(AXSTYLE_BOXOUT);

// Adjust the margins to fit the margin
$graph->setMargin(30, 100, 40, 30);

// Setup
$graph->title->Set('Basic contour plot with multiple axis');
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 12);

// A simple contour plot with default arguments (e.g. 10 isobar lines)
$cp = new ContourPlot($data, 10, 1);

// Display the legend
$cp->ShowLegend();

$graph->add($cp);

// ... and send the graph back to the browser
$graph->stroke();
