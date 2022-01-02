<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

function readsunspotdata($aFile, &$aYears, &$aSunspots): void
{
    $lines = @file($aFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (false === $lines) {
        throw new JpGraphException('Can not read sunspot data file.');
    }
    foreach ($lines as $line => $datarow) {
        $split       = preg_split('/[\s]+/', $datarow);
        $aYears[]    = mb_substr(trim($split[0]), 0, 4);
        $aSunspots[] = trim($split[1]);
    }
}

$year  = [];
$ydata = [];
readsunspotdata('yearssn.txt', $year, $ydata);

// Just keep the last 20 values in the arrays
$year  = array_slice($year, -20);
$ydata = array_slice($ydata, -20);

// Width and height of the graph
$width  = 600;
$height = 200;

// Create a graph instance
$graph = new Graph($width, $height);

// Specify what scale we want to use,
// text = txt scale for the X-axis
// int = integer scale for the Y-axis
$graph->setScale('textint');

// Setup a title for the graph
$graph->title->Set('Sunspot example');

// Setup titles and X-axis labels
$graph->xaxis->title->Set('(year)');
$graph->xaxis->SetTickLabels($year);

// Setup Y-axis title
$graph->yaxis->title->Set('(# sunspots)');

// Create the bar plot
$barplot = new BarPlot($ydata);

// Add the plot to the graph
$graph->add($barplot);

// Display the graph
$graph->stroke();
