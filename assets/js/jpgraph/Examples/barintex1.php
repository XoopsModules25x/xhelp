<?php
// content="text/plain; charset=utf-8"
// $Id: barintex1.php,v 1.3 2002/07/11 23:27:28 aditus Exp $
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

// Some data
$datay = [1, 1, 0.5];

// Create the graph and setup the basic parameters
$graph = new Graph(460, 200, 'auto');
$graph->clearTheme();
$graph->img->setMargin(40, 30, 30, 40);
$graph->setScale('textint');
$graph->setShadow();
$graph->setFrame(false); // No border around the graph

// Add some grace to the top so that the scale doesn't
// end exactly at the max value.
$graph->yaxis->scale->SetGrace(100);

// Setup X-axis labels
$a = $gDateLocale->getShortMonth();
$graph->xaxis->SetTickLabels($a);
$graph->xaxis->SetFont(FF_FONT2);

// Setup graph title ands fonts
$graph->title->Set('Example of integer Y-scale');
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
// Black color for positive values and darkred for negative values
$bplot->value->setColor('black', 'darkred');
$graph->add($bplot);

// Finally stroke the graph
$graph->stroke();
