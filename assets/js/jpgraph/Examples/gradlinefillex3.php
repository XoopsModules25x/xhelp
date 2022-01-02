<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$datay = [20, 10, 35, 5, 17, 35, 22];

// Setup the graph
$graph = new Graph(400, 200);
$graph->clearTheme();
$graph->setMargin(40, 40, 20, 30);
$graph->setScale('intlin');
$graph->setBox();
$graph->setMarginColor('darkgreen@0.8');

// Setup a background gradient image
$graph->setBackgroundGradient('darkred', 'yellow', GRAD_HOR, BGRAD_PLOT);

$graph->title->Set('Gradient filled line plot ex3');
$graph->yscale->SetAutoMin(0);

// Create the line
$p1 = new LinePlot($datay);
$p1->SetFillGradient('white', 'darkgreen', 4);
$graph->add($p1);

// Output line
$graph->stroke();
