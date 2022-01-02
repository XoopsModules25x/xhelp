<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

$datay = [17, 22, 33, 48, 24, 20];

// Create the graph. These two calls are always required
$graph = new Graph(220, 300, 'auto');
$graph->setScale('textlin');

$theme_class = new UniversalTheme();
$graph->setTheme($theme_class);

$graph->set90AndMargin(50, 40, 40, 40);
$graph->img->SetAngle(90);

// set major and minor tick positions manually
$graph->setBox(false);

//$graph->ygrid->SetColor('gray');
$graph->ygrid->Show(false);
$graph->ygrid->SetFill(false);
$graph->xaxis->SetTickLabels(['A', 'B', 'C', 'D', 'E', 'F']);
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false, false);

// For background to be gradient, setfill is needed first.
$graph->setBackgroundGradient('#00CED1', '#FFFFFF', GRAD_HOR, BGRAD_PLOT);

// Create the bar plots
$b1plot = new BarPlot($datay);

// ...and add it to the graPH
$graph->add($b1plot);

$b1plot->setWeight(0);
$b1plot->SetFillGradient('#808000', '#90EE90', GRAD_HOR);
$b1plot->SetWidth(17);

// Display the graph
$graph->stroke();
