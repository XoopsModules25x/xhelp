<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_date.php';

// Create a data set in range (50,70) and X-positions
define('NDATAPOINTS', 360);
define('SAMPLERATE', 240);
$start = time();
$end   = $start + NDATAPOINTS * SAMPLERATE;
$data  = [];
$xdata = [];
for ($i = 0; $i < NDATAPOINTS; ++$i) {
    $data[$i]  = mt_rand(50, 70);
    $xdata[$i] = $start + $i * SAMPLERATE;
}

// Create the new graph
$graph = new Graph(540, 300);

// Slightly larger than normal margins at the bottom to have room for
// the x-axis labels
$graph->setMargin(40, 40, 30, 130);

// Fix the Y-scale to go between [0,100] and use date for the x-axis
$graph->setScale('datlin', 0, 100);
$graph->title->Set('Example on Date scale');

// Set the angle for the labels to 90 degrees
$graph->xaxis->SetLabelAngle(90);

$line = new LinePlot($data, $xdata);
$line->setLegend('Year 2005');
$line->SetFillColor('lightblue@0.5');
$graph->add($line);
$graph->stroke();
