<?php
// content="text/plain; charset=utf-8"

require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

// We need some data
$datay = [4, 8, 6];

// Setup the graph.
$graph = new Graph(200, 150);
$graph->setScale('textlin');
$graph->img->setMargin(25, 15, 25, 25);

$graph->title->Set('"GRAD_RAISED_PANEL"');
$graph->title->SetColor('darkred');

// Setup font for axis
$graph->xaxis->SetFont(FF_FONT1);
$graph->yaxis->SetFont(FF_FONT1);

// Create the bar pot
$bplot = new BarPlot($datay);
$bplot->SetWidth(0.6);

// Setup color for gradient fill style
$bplot->SetFillGradient('navy', 'orange', GRAD_RAISED_PANEL);

// Set color for the frame of each bar
$bplot->setColor('navy');
$graph->add($bplot);

// Finally send the graph to the browser
$graph->stroke();
