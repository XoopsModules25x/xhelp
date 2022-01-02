<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

$datay = [12, 8, 19, 3, 10, 5];

// Create the graph. These two calls are always required
$graph = new Graph(300, 200);
$graph->clearTheme();
$graph->setScale('textlin');
$graph->yaxis->scale->SetGrace(20);

// Add a drop shadow
$graph->setShadow();

// Adjust the margin a bit to make more room for titles
$graph->img->setMargin(40, 30, 20, 40);

// Create a bar pot
$bplot = new BarPlot($datay);

// Adjust fill color
$bplot->SetFillColor('orange');
$bplot->value->show();
$bplot->value->setFont(FF_ARIAL, FS_BOLD, 10);
$bplot->value->setAngle(45);
$bplot->value->setFormat('%0.1f');
$graph->add($bplot);

// Setup the titles
$graph->title->Set('Bar graph with Y-scale grace');
$graph->xaxis->title->Set('X-title');
$graph->yaxis->title->Set('Y-title');

$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

// Display the graph
$graph->stroke();
