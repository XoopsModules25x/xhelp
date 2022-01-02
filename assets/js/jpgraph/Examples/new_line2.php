<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$datay1 = [20, 7, 16, 46];
$datay2 = [6, 20, 10, 22];

// Setup the graph
$graph = new Graph(350, 230);
$graph->setScale('textlin');

$theme_class = new UniversalTheme();
$graph->setTheme($theme_class);

$graph->title->Set('Background Image');
$graph->setBox(false);

$graph->yaxis->HideZeroLabel();
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false, false);

$graph->xaxis->SetTickLabels(['A', 'B', 'C', 'D']);
$graph->ygrid->SetFill(false);
$graph->setBackgroundImage('tiger_bkg.png', BGIMG_FILLFRAME);

$p1 = new LinePlot($datay1);
$graph->add($p1);

$p2 = new LinePlot($datay2);
$graph->add($p2);

$p1->SetColor('#55bbdd');
$p1->setLegend('Line 1');
$p1->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
$p1->mark->SetColor('#55bbdd');
$p1->mark->SetFillColor('#55bbdd');
$p1->setCenter();

$p2->SetColor('#aaaaaa');
$p2->setLegend('Line 2');
$p2->mark->SetType(MARK_UTRIANGLE, '', 1.0);
$p2->mark->SetColor('#aaaaaa');
$p2->mark->SetFillColor('#aaaaaa');
$p2->value->setMargin(14);
$p2->setCenter();

$graph->legend->SetFrameWeight(1);
$graph->legend->SetColor('#4E4E4E', '#00A78A');
$graph->legend->SetMarkAbsSize(8);

// Output line
$graph->stroke();

?>


