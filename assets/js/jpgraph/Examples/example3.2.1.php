<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$ydata = [11, 3, 8, 12, 5, 1, 9, 15, 5, 7];

// Create the graph. These two calls are always required
$graph = new Graph(300, 200);
$graph->clearTheme();
$graph->setScale('textlin');
$graph->yaxis->scale->SetGrace(10, 10);

// Create the linear plot
$lineplot = new LinePlot($ydata);
$lineplot->mark->SetType(MARK_CIRCLE);

// Add the plot to the graph
$graph->add($lineplot);

$graph->img->setMargin(40, 20, 20, 40);
$graph->title->Set('Grace value, version 1');
$graph->xaxis->title->Set('X-title');
$graph->yaxis->title->Set('Y-title');

$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

$lineplot->SetColor('blue');
$lineplot->setWeight(2);
$graph->yaxis->SetWeight(2);
$graph->setShadow();

// Display the graph
$graph->stroke();
