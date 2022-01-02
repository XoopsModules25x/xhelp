<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

// Some data
$datay = [7, 19, 11, 4, 20];

// Create the graph and setup the basic parameters
$graph = new Graph(300, 200, 'auto');
$graph->clearTheme();
$graph->img->setMargin(40, 30, 40, 50);
$graph->setScale('textint');
$graph->setFrame(true, 'blue', 1);
$graph->setColor('lightblue');
$graph->setMarginColor('lightblue');

// Setup X-axis labels
$a = $gDateLocale->getShortMonth();
$graph->xaxis->SetTickLabels($a);
$graph->xaxis->SetFont(FF_FONT1);
$graph->xaxis->SetColor('darkblue', 'black');

// Setup "hidden" y-axis by given it the same color
// as the background (this could also be done by setting the weight
// to zero)
$graph->yaxis->SetColor('lightblue', 'darkblue');
$graph->ygrid->SetColor('white');

// Setup graph title ands fonts
$graph->title->Set('Using grace = 10%');
$graph->title->SetFont(FF_FONT2, FS_BOLD);
$graph->xaxis->SetTitle('Year 2002', 'center');
$graph->xaxis->SetTitleMargin(10);
$graph->xaxis->title->SetFont(FF_FONT2, FS_BOLD);

// Add some grace to the top so that the scale doesn't
// end exactly at the max value.
$graph->yaxis->scale->SetGrace(10);

// Create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillColor('darkblue');
$bplot->setColor('darkblue');
$bplot->SetWidth(0.5);
$bplot->SetShadow('darkgray');

// Setup the values that are displayed on top of each bar
// Must use TTF fonts if we want text at an arbitrary angle
$bplot->value->show();
$bplot->value->setFont(FF_ARIAL, FS_NORMAL, 8);
$bplot->value->setFormat('$%d');
$bplot->value->setColor('darkred');
$bplot->value->setAngle(45);
$graph->add($bplot);

// Finally stroke the graph
$graph->stroke();
