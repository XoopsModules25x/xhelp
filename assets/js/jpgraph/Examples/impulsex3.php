<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_scatter.php';

$numpoints = 50;
$k         = 0.05;

// Create some data points
for ($i = 0; $i < $numpoints; ++$i) {
    $datay[$i] = exp(-$k * $i) * cos(2 * M_PI / 10 * $i);
}

// A format callbakc function
function mycallback($l)
{
    return sprintf('%02.2f', $l);
}

// Setup the basic parameters for the graph
$graph = new Graph(400, 200);
$graph->setScale('intlin');
$graph->setShadow();
$graph->setBox();

$graph->title->Set('Impuls Example 3');
$graph->title->SetFont(FF_FONT1, FS_BOLD);

// Set format callback for labels
$graph->yaxis->SetLabelFormatCallback('mycallback');

// Set X-axis at the minimum value of Y-axis (default will be at 0)
$graph->xaxis->SetPos('min');   // "min" will position the x-axis at the minimum value of the Y-axis

// Extend the margin for the labels on the Y-axis and reverse the direction
// of the ticks on the Y-axis
$graph->yaxis->SetLabelMargin(12);
$graph->xaxis->SetLabelMargin(6);
$graph->yaxis->SetTickSide(SIDE_LEFT);
$graph->xaxis->SetTickSide(SIDE_DOWN);

// Create a new impuls type scatter plot
$sp1 = new ScatterPlot($datay);
$sp1->mark->SetType(MARK_SQUARE);
$sp1->mark->SetFillColor('red');
$sp1->SetImpuls();
$sp1->setColor('blue');
$sp1->setWeight(1);
$sp1->mark->SetWidth(3);

$graph->add($sp1);

$graph->stroke();
