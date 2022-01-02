<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

$l1datay = [11, 9, 2, 4, 3, 13, 17];
$l2datay = [23, 12, 5, 19, 17, 10, 15];
JpGraphError::setImageFlag(false);
JpGraphError::setLogFile('syslog');

// Create the graph.
$graph = new Graph(400, 200);
$graph->clearTheme();
$graph->setScale('intlin');

$graph->img->setMargin(40, 130, 20, 40);
$graph->setShadow();

// Create the linear error plot
$l1plot = new LinePlot($l1datay);
$l1plot->SetColor('red');
$l1plot->setWeight(2);
$l1plot->setLegend('Prediction');

// Create the bar plot
$bplot = new BarPlot($l2datay);
$bplot->SetFillColor('orange');
$bplot->setLegend('Result');

// Add the plots to t'he graph
$graph->add($bplot);
$graph->add($l1plot);

$graph->title->Set('Adding a line plot to a bar graph v3');
$graph->xaxis->title->Set('X-title');
$graph->yaxis->title->Set('Y-title');

$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

$datax = $gDateLocale->getShortMonth();
$graph->xaxis->SetTickLabels($datax);

// Display the graph
$graph->stroke();
