<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

$datay = [10, 29, 3, 6];

// Create the graph.
$graph = new Graph(200, 200);
$graph->setScale('textlin');
$graph->setMargin(25, 10, 20, 25);
$graph->setBox(true);

// Add 10% grace ("space") at top and botton of Y-scale.
$graph->yscale->SetGrace(10);

// Create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillColor('lightblue');

$graph->ygrid->Show(false);

// .. and add the plot to the graph
$graph->add($bplot);

// Add band
$band = new PlotBand(HORIZONTAL, BAND_3DPLANE, 15, 35, 'khaki4');
$band->SetDensity(10);
$band->ShowFrame(true);
$graph->addBand($band);

// Set title
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 10);
$graph->title->SetColor('darkred');
$graph->title->Set('BAND_3DPLANE, Density=10');

$graph->stroke();
