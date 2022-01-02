<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$datay = [1.23, 1.9, 1.6, 3.1, 3.4, 2.8, 2.1, 1.9];
$graph = new Graph(300, 200);
$graph->clearTheme();
$graph->setScale('textlin');

$graph->img->setMargin(40, 40, 40, 40);
$graph->setShadow();
$graph->setGridDepth(DEPTH_FRONT);

$graph->title->Set('Example of filled line plot');
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$p1 = new LinePlot($datay);
$p1->SetFillColor('orange');
$p1->mark->SetType(MARK_FILLEDCIRCLE);
$p1->mark->SetFillColor('red');
$p1->mark->SetWidth(4);
$graph->add($p1);

$graph->stroke();
