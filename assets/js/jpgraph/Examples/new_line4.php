<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_scatter.php';

$datay1 = [33, 20, 24, 5, 38, 24, 22];
$datay2 = [9, 7, 10, 25, 10, 8, 4];

// Setup the graph
$graph = new Graph(300, 250);

$graph->setScale('textlin', 0, 50);

$theme_class = new UniversalTheme();
$graph->setTheme($theme_class);

$graph->title->Set('Line Plots with Markers');

$graph->setBox(false);
$graph->ygrid->SetFill(false);
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false, false);
$graph->yaxis->HideZeroLabel();

$graph->xaxis->SetTickLabels(['A', 'B', 'C', 'D', 'E', 'F', 'G']);
// Create the plot
$p1 = new LinePlot($datay1);
$graph->add($p1);

$p2 = new LinePlot($datay2);
$graph->add($p2);

// Use an image of favourite car as marker
$p1->mark->SetType(MARK_IMG, 'new1.gif', 0.8);
$p1->SetColor('#aadddd');
$p1->value->setFormat('%d');
$p1->value->show();
$p1->value->setColor('#55bbdd');

$p2->mark->SetType(MARK_IMG, 'new2.gif', 0.8);
$p2->SetColor('#ddaa99');
$p2->value->setFormat('%d');
$p2->value->show();
$p2->value->setColor('#55bbdd');

$graph->stroke();
