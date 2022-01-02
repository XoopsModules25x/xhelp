<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_scatter.php';

$datay = [20, 22, 12, 13, 17, 20, 16, 19, 30, 31, 40, 43];

$graph = new Graph(300, 200);
$graph->clearTheme();
$graph->setScale('textlin');

$graph->setShadow();
$graph->img->setMargin(40, 40, 40, 40);

$graph->title->Set('Impuls plot, variant 2');
$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->title->Set('Impuls respons');
$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

$sp1 = new ScatterPlot($datay); //,$datax);
$sp1->mark->SetType(MARK_FILLEDCIRCLE);
$sp1->mark->SetFillColor('red');
$sp1->mark->SetWidth(4);
$sp1->SetImpuls();
$sp1->setColor('blue');
$sp1->setWeight(3);

$graph->add($sp1);
$graph->stroke();
