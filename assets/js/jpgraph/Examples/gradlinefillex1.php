<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$datay = [20, 15, 33, 5, 17, 35, 22];

// Setup the graph
$graph = new Graph(400, 200);
$graph->clearTheme();
$graph->setMargin(40, 40, 20, 30);
$graph->setScale('intlin');
$graph->setMarginColor('darkgreen@0.8');

$graph->title->Set('Gradient filled line plot');
$graph->yscale->SetAutoMin(0);

// Create the line
$p1 = new LinePlot($datay);
$p1->SetColor('blue');
$p1->setWeight(0);
$p1->SetFillGradient('red', 'yellow');

$graph->add($p1);

// Output line
$graph->stroke();
