<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$ydata = [11, 3, 8, 12, 5, 1, 9, 13, 5, 7];

// Create the graph. These two calls are always required
$graph = new Graph(300, 250);
$graph->clearTheme();
$graph->setScale('intlin', 0, 10);
$graph->setMargin(30, 20, 70, 40);
$graph->setMarginColor([177, 191, 174]);

$graph->setClipping(true);

$graph->xaxis->SetFont(FF_FONT1, FS_BOLD);

$graph->ygrid->SetLineStyle('dashed');

$graph->title->Set('Manual scale');
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 14);
$graph->title->SetColor('white');
$graph->subtitle->Set('(With clipping)');
$graph->subtitle->SetColor('white');
$graph->subtitle->SetFont(FF_ARIAL, FS_BOLD, 10);

// Create the linear plot
$lineplot = new LinePlot($ydata);
$lineplot->SetColor('red');
$lineplot->setWeight(2);

// Add the plot to the graph
$graph->add($lineplot);

// Display the graph
$graph->stroke();
