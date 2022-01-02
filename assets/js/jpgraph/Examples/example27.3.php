<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_pie.php';
require_once __DIR__ . '/jpgraph/jpgraph_pie3d.php';

$data = [40, 60, 21, 33];

$graph = new PieGraph(330, 200);
$graph->clearTheme();
$graph->setShadow();

$graph->title->Set('A simple 3D Pie plot');
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$p1 = new PiePlot3D($data);
$p1->ExplodeSlice(1);
$p1->SetCenter(0.45);
$p1->SetLegends($gDateLocale->getShortMonth());

$graph->Add($p1);
$graph->Stroke();
