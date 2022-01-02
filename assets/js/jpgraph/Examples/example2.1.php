<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$ydata = [11, -3, -8, 7, 5, -1, 9, 13, 5, -7];

// Create the graph. These two calls are always required
$graph = new Graph(300, 200);
$graph->setScale('textlin');

// Create the linear plot
$lineplot = new LinePlot($ydata);

$lineplot->value->show();
$lineplot->value->setColor('red');
$lineplot->value->setFont(FF_FONT1, FS_BOLD);

// Add the plot to the graph
$graph->add($lineplot);

$graph->img->setMargin(40, 20, 20, 40);
$graph->title->Set('Example 2.1');
$graph->xaxis->title->Set('X-title');
$graph->yaxis->title->Set('Y-title');

// Display the graph
$graph->stroke();
