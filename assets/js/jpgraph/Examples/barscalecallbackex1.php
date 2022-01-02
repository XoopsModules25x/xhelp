<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

// Callback function for Y-scale to get 1000 separator on labels
function separator1000($aVal)
{
    return number_format($aVal);
}

function separator1000_usd($aVal)
{
    return '$' . number_format($aVal);
}

// Some data
$datay = [120567, 134013, 192000, 87000];

// Create the graph and setup the basic parameters
$graph = new Graph(500, 300, 'auto');
$graph->clearTheme();
$graph->img->setMargin(80, 30, 30, 40);
$graph->setScale('textint');
$graph->setShadow();
$graph->setFrame(false); // No border around the graph

// Add some grace to the top so that the scale doesn't
// end exactly at the max value.
// The grace value is the percetage of additional scale
// value we add. Specifying 50 means that we add 50% of the
// max value
$graph->yaxis->scale->SetGrace(50);
$graph->yaxis->SetLabelFormatCallback('separator1000');

// Setup X-axis labels
$a = $gDateLocale->getShortMonth();
$graph->xaxis->SetTickLabels($a);
$graph->xaxis->SetFont(FF_FONT2);

// Setup graph title ands fonts
$graph->title->Set('Example of Y-scale callback formatting');
$graph->title->SetFont(FF_FONT2, FS_BOLD);
$graph->xaxis->title->Set('Year 2002');
$graph->xaxis->title->SetFont(FF_FONT2, FS_BOLD);

// Create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillColor('orange');
$bplot->SetWidth(0.5);
$bplot->SetShadow();

// Setup the values that are displayed on top of each bar
$bplot->value->show();

// Must use TTF fonts if we want text at an arbitrary angle
$bplot->value->setFont(FF_ARIAL, FS_BOLD);
$bplot->value->setAngle(45);
$bplot->value->setFormatCallback('separator1000_usd');

// Black color for positive values and darkred for negative values
$bplot->value->setColor('black', 'darkred');
$graph->add($bplot);

// Finally stroke the graph
$graph->stroke();
