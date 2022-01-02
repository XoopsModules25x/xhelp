<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$ydata = [11, 3, 8, 12, 5, 1, 9, 13, 5, 7];

// Create the graph. These two calls are always required
$graph = new Graph(300, 200);
$graph->setScale('textlin');
$graph->img->setMargin(50, 90, 40, 50);
$graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
$graph->title->Set('Examples for graph');

// Create the linear plot
$lineplot = new LinePlot($ydata);
$lineplot->setLegend('Test 1');
$lineplot->SetColor('blue');

// Add the plot to the graph
$graph->add($lineplot);

// Display the graph
$graph->stroke();
