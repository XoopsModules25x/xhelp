<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$datay = [0, 25, 12, 47, 27, 27, 0];

// Setup the graph
$graph = new Graph(350, 250);
$graph->setScale('intlin', 0, $aYMax = 50);

$theme_class = new UniversalTheme();
$graph->setTheme($theme_class);

$graph->setMargin(40, 40, 50, 40);

$graph->title->Set('Inverted Y-axis');
$graph->setBox(false);
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false, false);

// For background to be gradient, setfill is needed first.
$graph->ygrid->SetFill(true, '#FFFFFF@0.5', '#FFFFFF@0.5');
$graph->setBackgroundGradient('#FFFFFF', '#00FF7F', GRAD_HOR, BGRAD_PLOT);

$graph->xaxis->SetTickLabels(['G', 'F', 'E', 'D', 'C', 'B', 'A']);
$graph->xaxis->SetLabelMargin(20);
$graph->yaxis->SetLabelMargin(20);

$graph->setAxisStyle(AXSTYLE_BOXOUT);
$graph->img->SetAngle(180);

// Create the line
$p1 = new LinePlot($datay);
$graph->add($p1);

$p1->SetFillGradient('#FFFFFF', '#F0F8FF');
$p1->SetColor('#aadddd');

// Output line
$graph->stroke();

?>


