<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

$datay1 = [13, 8, 19, 7, 17, 6];
$datay2 = [4, 5, 2, 7, 5, 25];

// Create the graph.
$graph = new Graph(350, 250);
$graph->setScale('textlin');
$graph->setMarginColor('white');

// Setup title
$graph->title->Set('Acc bar with gradient');

// Create the first bar
$bplot = new BarPlot($datay1);
$bplot->SetFillGradient('AntiqueWhite2', 'AntiqueWhite4:0.8', GRAD_VERT);
$bplot->setColor('darkred');
$bplot->setWeight(0);

// Create the second bar
$bplot2 = new BarPlot($datay2);
$bplot2->SetFillGradient('olivedrab1', 'olivedrab4', GRAD_VERT);
$bplot2->setColor('darkgreen');
$bplot2->setWeight(0);

// And join them in an accumulated bar
$accbplot = new AccBarPlot([$bplot, $bplot2]);
$accbplot->setColor('darkgray');
$accbplot->setWeight(1);
$graph->add($accbplot);

$graph->stroke();
