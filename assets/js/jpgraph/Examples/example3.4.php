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
$graph->clearTheme();
$graph->setScale('intlin');
$graph->setShadow();

// Setup margin and titles
$graph->setMargin(40, 20, 20, 40);
$graph->title->Set('Calls per operator');
$graph->subtitle->Set('(March 12, 2008)');
$graph->xaxis->title->Set('Operator');
$graph->yaxis->title->Set('# of calls');

$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

$graph->yaxis->SetColor('blue');

// Create the linear plot
$lineplot = new LinePlot($ydata);
$lineplot->SetColor('blue');
$lineplot->setWeight(2);   // Two pixel wide
$lineplot->mark->SetType(MARK_UTRIANGLE);
$lineplot->mark->SetColor('blue');
$lineplot->mark->SetFillColor('red');

$lineplot->value->show();
$lineplot->value->setFont(FF_ARIAL, FS_BOLD, 10);
$lineplot->value->setColor('darkred');
$lineplot->value->setFormat('(%d)');

// Add the plot to the graph
$graph->add($lineplot);

// Display the graph
$graph->stroke();
