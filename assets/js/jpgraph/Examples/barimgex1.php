<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$datay = [5, 3, 11, 6, 3];

$graph = new Graph(400, 300, 'auto');
$graph->clearTheme();
$graph->setScale('textlin');

$graph->title->Set('Images on top of bars');
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 13);

$graph->setTitleBackground('lightblue:1.1', TITLEBKG_STYLE1, TITLEBKG_FRAME_BEVEL);

$bplot = new BarPlot($datay);
$bplot->SetFillColor('orange');
$bplot->SetWidth(0.5);

$lplot = new LinePlot($datay);
$lplot->SetColor('white@1');
$lplot->SetBarCenter();
$lplot->mark->SetType(MARK_IMG_LBALL, 'red');

$graph->add($bplot);
$graph->add($lplot);

$graph->stroke();
