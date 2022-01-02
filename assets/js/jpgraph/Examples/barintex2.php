<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

// Some data
$datay = [3, 7, 19, 11, 4, 20];

// Create the graph and setup the basic parameters
$graph = new Graph(350, 200, 'auto');
$graph->clearTheme();
$graph->img->setMargin(40, 30, 40, 40);
$graph->setScale('textint');
$graph->setFrame(true, 'blue', 1);
$graph->setColor('lightblue');
$graph->setMarginColor('lightblue');

// Add some grace to the top so that the scale doesn't
// end exactly at the max value.
//$graph->yaxis->scale->SetGrace(20);

// Setup X-axis labels
$a = $gDateLocale->getShortMonth();
$graph->xaxis->SetTickLabels($a);
$graph->xaxis->SetFont(FF_FONT1);
$graph->xaxis->SetColor('darkblue', 'black');

// Stup "hidden" y-axis by given it the same color
// as the background
$graph->yaxis->SetColor('lightblue', 'darkblue');
$graph->ygrid->SetColor('white');

// Setup graph title ands fonts
$graph->title->Set('Example of integer Y-scale');
$graph->subtitle->Set('(With "hidden" y-axis)');

$graph->title->SetFont(FF_FONT2, FS_BOLD);
$graph->xaxis->title->Set('Year 2002');
$graph->xaxis->title->SetFont(FF_FONT2, FS_BOLD);

// Create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillColor('darkblue');
$bplot->setColor('darkblue');
$bplot->SetWidth(0.5);
$bplot->SetShadow('darkgray');

// Setup the values that are displayed on top of each bar
$bplot->value->show();
// Must use TTF fonts if we want text at an arbitrary angle
$bplot->value->setFont(FF_ARIAL, FS_NORMAL, 8);
$bplot->value->setFormat('$%d');
// Black color for positive values and darkred for negative values
$bplot->value->setColor('black', 'darkred');
$graph->add($bplot);

// Finally stroke the graph
$graph->stroke();
