<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

// Some (random) data
$ydata = [17, 3, '', 10, 7, '', 3, 19, 9, 7];

// Size of the overall graph
$width  = 350;
$height = 250;

// Create the graph and set a scale.
// These two calls are always required
$graph = new Graph($width, $height);
$graph->clearTheme();
$graph->setScale('intlin');
$graph->setShadow();

// Setup margin and titles
$graph->setMargin(40, 20, 20, 40);
$graph->title->Set('NULL values');
$graph->xaxis->title->Set('x-title');
$graph->yaxis->title->Set('y-title');

$graph->yaxis->title->SetFont(FF_ARIAL, FS_BOLD, 9);
$graph->xaxis->title->SetFont(FF_ARIAL, FS_BOLD, 9);

$graph->yaxis->SetColor('blue');

// Create the linear plot
$lineplot = new LinePlot($ydata);
$lineplot->SetColor('blue');
$lineplot->setWeight(2);   // Two pixel wide

// Add the plot to the graph
$graph->add($lineplot);

// Display the graph
$graph->stroke();
