<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$ydata = [12, 19, 3, 9, 15, 10];

// The code to setup a very basic graph
$graph = new Graph(200, 150);
$graph->setScale('intlin');
$graph->setMargin(30, 15, 40, 30);
$graph->setMarginColor('white');
$graph->setFrame(true, 'blue', 3);

$graph->title->Set('Label background');
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 12);

$graph->subtitle->SetFont(FF_ARIAL, FS_NORMAL, 10);
$graph->subtitle->SetColor('darkred');
$graph->subtitle->Set('"LABELBKG_XYFULL"');

$graph->setAxisLabelBackground(LABELBKG_XYFULL, 'orange', 'red', 'lightblue', 'red');

// Use Ariel font
$graph->xaxis->SetFont(FF_ARIAL, FS_NORMAL, 9);
$graph->yaxis->SetFont(FF_ARIAL, FS_NORMAL, 9);
$graph->xgrid->Show();

// Create the plot line
$p1 = new LinePlot($ydata);
$graph->add($p1);

// Output graph
$graph->stroke();

?>


