<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_pie.php';

$data = [40, 60, 21, 33, 12, 33];

$graph = new PieGraph(150, 150);
$graph->clearTheme();
$graph->setShadow();

$graph->title->Set("'earth' Theme");
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$p1 = new PiePlot($data);
$p1->SetTheme('earth');
$p1->SetCenter(0.5, 0.55);
$p1->value->show(false);
$graph->Add($p1);
$graph->Stroke();
