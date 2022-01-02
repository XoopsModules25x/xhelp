<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$datay = [20, 10, 35, 5, 17, 35, 22];

// Setup the graph
$graph = new Graph(400, 250);
$graph->setScale('intlin', 0, $aYMax = 50);
$theme_class = new UniversalTheme();
$graph->setTheme($theme_class);

$graph->setBox(false);

$graph->title->Set('Step Line');
$graph->ygrid->Show(true);
$graph->xgrid->Show(false);
$graph->yaxis->HideZeroLabel();
$graph->ygrid->SetFill(true, '#FFFFFF@0.5', '#FFFFFF@0.5');
$graph->setBackgroundGradient('blue', '#55eeff', GRAD_HOR, BGRAD_PLOT);
$graph->xaxis->SetTickLabels(['A', 'B', 'C', 'D', 'E', 'F', 'G']);

// Create the line
$p1 = new LinePlot($datay);
$graph->add($p1);

$p1->SetFillGradient('yellow', 'red');
$p1->SetStepStyle();
$p1->SetColor('#808000');

// Output line
$graph->stroke();

?>


