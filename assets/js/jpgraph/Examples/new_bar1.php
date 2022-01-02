<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

$data1y = [47, 80, 40, 116];
$data2y = [61, 30, 82, 105];
$data3y = [115, 50, 70, 93];

// Create the graph. These two calls are always required
$graph = new Graph(350, 200, 'auto');
$graph->setScale('textlin');

$theme_class = new UniversalTheme();
$graph->setTheme($theme_class);

$graph->yaxis->SetTickPositions([0, 30, 60, 90, 120, 150], [15, 45, 75, 105, 135]);
$graph->setBox(false);

$graph->ygrid->SetFill(false);
$graph->xaxis->SetTickLabels(['A', 'B', 'C', 'D']);
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false, false);

// Create the bar plots
$b1plot = new BarPlot($data1y);
$b2plot = new BarPlot($data2y);
$b3plot = new BarPlot($data3y);

// Create the grouped bar plot
$gbplot = new GroupBarPlot([$b1plot, $b2plot, $b3plot]);
// ...and add it to the graPH
$graph->add($gbplot);

$b1plot->setColor('white');
$b1plot->SetFillColor('#cc1111');

$b2plot->setColor('white');
$b2plot->SetFillColor('#11cccc');

$b3plot->setColor('white');
$b3plot->SetFillColor('#1111cc');

$graph->title->Set('Bar Plots');

// Display the graph
$graph->stroke();
