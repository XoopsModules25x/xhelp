<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$ydata = [11, 11, 11];

// Create the graph.
$graph = new Graph(350, 250);
$graph->clearTheme();
$graph->setScale('textlin');
$graph->img->setMargin(30, 90, 40, 50);
$graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
$graph->title->Set('Example 1.1 same y-values');

// Create the linear plot
$lineplot = new LinePlot($ydata);
$lineplot->setLegend('Test 1');
$lineplot->SetColor('blue');
$lineplot->setWeight(5);

// Add the plot to the graph
$graph->add($lineplot);

// Display the graph
$graph->stroke();
