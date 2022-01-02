<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$datay = [11, 30, 20, 13, 10, 'x', 16, 12, 'x', 15, 4, 9];

// Setup the graph
$graph = new Graph(400, 250);
$graph->setScale('intlin');
$graph->title->Set('Filled line with NULL values');
//Make sure data starts from Zero whatever data we have
$graph->yscale->SetAutoMin(0);

$p1 = new LinePlot($datay);
$p1->SetFillColor('lightblue');
$graph->add($p1);

// Output line
$graph->stroke();

?>


