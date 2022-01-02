<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_pie.php';
require_once __DIR__ . '/jpgraph/jpgraph_pie3d.php';

// Some data
$data = [40, 60, 21, 33];

// Create the Pie Graph.
$graph = new PieGraph(350, 250);

$theme_class = new UniversalTheme();
$graph->setTheme($theme_class);

// Set A title for the plot
$graph->title->Set('A Simple 3D Pie Plot');

// Create
$p1 = new PiePlot3D($data);
$graph->Add($p1);

$p1->ShowBorder();
$p1->SetColor('black');
$p1->SetSliceColors(['#1E90FF', '#2E8B57', '#ADFF2F', '#BA55D3']);
$p1->ExplodeSlice(1);
$graph->Stroke();
