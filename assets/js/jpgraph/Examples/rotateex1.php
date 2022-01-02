<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$ydata  = [11, 3, 8, 12, 5, 1, 9, 13, 5, 7];
$y2data = [354, 200, 265, 99, 111, 91, 198, 225, 293, 251];

$graph = new Graph(350, 300);
$graph->clearTheme();
$graph->setAngle(40);
$graph->img->setMargin(80, 80, 80, 80);
$graph->setScale('textlin');
$graph->setY2Scale('lin');
$graph->setShadow();

// Create the linear plot
$lineplot  = new LinePlot($ydata);
$lineplot2 = new LinePlot($y2data);

// Add the plot to the graph
$graph->add($lineplot);
$graph->addY2($lineplot2);
$lineplot2->SetColor('orange');
$lineplot2->setWeight(2);
$graph->y2axis->SetColor('orange');

$graph->title->Set('Example 1 rotated graph (40 degree)');
$graph->legend->Pos(0.05, 0.1, 'right', 'top');

$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

$lineplot->SetColor('blue');
$lineplot->setWeight(2);

$lineplot2->SetColor('orange');
$lineplot2->setWeight(2);

$graph->yaxis->SetColor('blue');

$lineplot->setLegend('Plot 1');
$lineplot2->setLegend('Plot 2');

// Display the graph
$graph->stroke();
