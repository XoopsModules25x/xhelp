<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_log.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$ydata  = [11, 3, 8, 12, 5, 1, 9, 13, 5, 7];
$y2data = [354, 200, 265, 99, 111, 91, 198, 225, 293, 251];

// Create the graph. These two calls are always required
$graph = new Graph(350, 200);
$graph->clearTheme();
$graph->setScale('textlog');
$graph->setY2Scale('log');

$graph->setShadow();
$graph->setMargin(40, 110, 20, 40);

$graph->ygrid->Show(true, true);
$graph->xgrid->Show(true, false);

// Create the linear plot
$lineplot = new LinePlot($ydata);

$lineplot2 = new LinePlot($y2data);
$lineplot2->SetColor('orange');
$lineplot2->setWeight(2);

$graph->yaxis->scale->ticks->SupressFirst();

// Add the plot to the graph
$graph->add($lineplot);
$graph->addY2($lineplot2);

$graph->title->Set('Example 8');
$graph->xaxis->title->Set('X-title');
$graph->yaxis->title->Set('Y-title');

$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

$lineplot->SetColor('blue');
$lineplot->setWeight(2);

$lineplot2->SetColor('orange');
$lineplot2->setWeight(2);

$graph->yaxis->SetColor('blue');
$graph->y2axis->SetColor('orange');

$lineplot->setLegend('Plot 1');
$lineplot2->setLegend('Plot 2');

$graph->legend->Pos(0.05, 0.5, 'right', 'center');

// Display the graph
$graph->stroke();
