<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

// Some (random) data
$ydata = [11, 3, 8, 12, 5, 1, 9, 13, 5, 7];

// Size of the overall graph
$width  = 350;
$height = 250;

// Create the graph and set a scale.
// These two calls are always required
$graph = new Graph($width, $height);
$graph->setScale('intlin');

// Create the linear plot
$lineplot = new LinePlot($ydata);

// Add the plot to the graph
$graph->add($lineplot);

// Display the graph
$graph->stroke();
