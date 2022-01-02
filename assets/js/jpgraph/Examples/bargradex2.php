<?php
// content="text/plain; charset=utf-8"
// Example for use of JpGraph,
// ljp, 01/03/01 20:32
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

// We need some data
$datay = [-0.13, 0.25, -0.21, 0.35, 0.31, 0.04];
$datax = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'June'];

// Setup the graph.
$graph = new Graph(400, 200);
$graph->clearTheme();
$graph->img->setMargin(60, 20, 30, 50);
$graph->setScale('textlin');
$graph->setMarginColor('silver');
$graph->setShadow();

// Set up the title for the graph
$graph->title->Set('Example negative bars');
$graph->title->SetFont(FF_VERDANA, FS_NORMAL, 18);
$graph->title->SetColor('darkred');

// Setup font for axis
$graph->xaxis->SetFont(FF_VERDANA, FS_NORMAL, 12);
$graph->xaxis->SetColor('black', 'red');
$graph->yaxis->SetFont(FF_VERDANA, FS_NORMAL, 11);

// Show 0 label on Y-axis (default is not to show)
$graph->yscale->ticks->SupressZeroLabel(false);

// Setup X-axis labels
$graph->xaxis->SetTickLabels($datax);
$graph->xaxis->SetLabelAngle(50);

// Create the bar pot
$bplot = new BarPlot($datay);
$bplot->SetWidth(0.6);

// Setup color for gradient fill style
$bplot->SetFillGradient('navy', 'steelblue', GRAD_MIDVER);

// Set color for the frame of each bar
$bplot->setColor('navy');
$graph->add($bplot);

// Finally send the graph to the browser
$graph->stroke();
