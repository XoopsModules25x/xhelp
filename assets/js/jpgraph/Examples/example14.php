<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_error.php';

$errdatay = [11, 9, 2, 4, 19, 26, 13, 19, 7, 12];

// Create the graph. These two calls are always required
$graph = new Graph(300, 200);
$graph->clearTheme();
$graph->setScale('textlin');

$graph->img->setMargin(40, 30, 20, 40);
$graph->setShadow();

// Create the error plot
$errplot = new ErrorPlot($errdatay);
$errplot->setColor('red');
$errplot->setWeight(2);
$errplot->setCenter();

// Add the plot to the graph
$graph->add($errplot);

$graph->title->Set('Simple error plot');
$graph->xaxis->title->Set('X-title');
$graph->yaxis->title->Set('Y-title');

$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

$datax = $gDateLocale->getShortMonth();
$graph->xaxis->SetTickLabels($datax);

// Display the graph
$graph->stroke();
