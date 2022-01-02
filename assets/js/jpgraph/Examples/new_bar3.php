<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

$datay = [62, 105, 85, 50];

// Create the graph. These two calls are always required
$graph = new Graph(350, 220, 'auto');
$graph->setScale('textlin');

//$theme_class="DefaultTheme";
//$graph->SetTheme(new $theme_class());

// set major and minor tick positions manually
$graph->yaxis->SetTickPositions([0, 30, 60, 90, 120, 150], [15, 45, 75, 105, 135]);
$graph->setBox(false);

//$graph->ygrid->SetColor('gray');
$graph->ygrid->SetFill(false);
$graph->xaxis->SetTickLabels(['A', 'B', 'C', 'D']);
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false, false);

// Create the bar plots
$b1plot = new BarPlot($datay);

// ...and add it to the graPH
$graph->add($b1plot);

$b1plot->setColor('white');
$b1plot->SetFillGradient('#4B0082', 'white', GRAD_LEFT_REFLECTION);
$b1plot->SetWidth(45);
$graph->title->Set('Bar Gradient(Left reflection)');

// Display the graph
$graph->stroke();
