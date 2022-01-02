<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_pie.php';

$data = [40, 60, 21, 33];

$graph = new PieGraph(300, 200);
$graph->clearTheme();
$graph->setShadow();

$graph->title->Set('A simple Pie plot');

$p1 = new PiePlot($data);
$graph->Add($p1);
$graph->Stroke();
