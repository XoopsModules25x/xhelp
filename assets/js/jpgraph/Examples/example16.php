<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_error.php';

$errdatay = [11, 9, 2, 4, 19, 26, 13, 19, 7, 12];

// Create the graph. These two calls are always required
$graph = new Graph(300, 200);
$graph->clearTheme();
$graph->setScale('textlin');

$graph->img->setMargin(40, 30, 20, 40);
$graph->setShadow();

// Create the linear plot
$errplot = new ErrorLinePlot($errdatay);
$errplot->setColor('red');
$errplot->setWeight(2);
$errplot->setCenter();
$errplot->line->setWeight(2);
$errplot->line->SetColor('blue');

// Setup the legends
$errplot->setLegend('Min/Max');
$errplot->line->setLegend('Average');

// Add the plot to the graph
$graph->add($errplot);

$graph->title->Set('Linear error plot');
$graph->xaxis->title->Set('X-title');
$graph->yaxis->title->Set('Y-title');

$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

$datax = $gDateLocale->getShortMonth();
$graph->xaxis->SetTickLabels($datax);

// Display the graph
$graph->stroke();
