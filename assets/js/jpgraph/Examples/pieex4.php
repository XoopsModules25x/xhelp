<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_pie.php';

$data = [40, 60, 21, 33];

$graph = new PieGraph(300, 200);
$graph->clearTheme();
$graph->setShadow();

$graph->title->Set('Example 4 of pie plot');
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$p1 = new PiePlot($data);
$p1->value->setFont(FF_FONT1, FS_BOLD);
$p1->value->setColor('darkred');
$p1->SetSize(0.3);
$p1->SetCenter(0.4);
$p1->SetLegends(['Jan', 'Feb', 'Mar', 'Apr', 'May']);
$graph->Add($p1);

$graph->Stroke();
