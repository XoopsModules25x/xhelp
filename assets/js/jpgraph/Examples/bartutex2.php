<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

// Some data
$databary = [12, 7, 16, 6, 7, 14, 9, 3];
$months   = $gDateLocale->getShortMonth();

// New graph with a drop shadow
$graph = new Graph(300, 200, 'auto');
$graph->clearTheme();
$graph->setShadow();

// Use a "text" X-scale
$graph->setScale('textlin');

// Specify X-labels
$graph->xaxis->SetTickLabels($months);

// Set title and subtitle
$graph->title->Set('Textscale with specified labels');

// Use built in font
$graph->title->SetFont(FF_FONT1, FS_BOLD);

// Create the bar plot
$b1 = new BarPlot($databary);
$b1->setLegend('Temperature');

//$b1->SetAbsWidth(6);
//$b1->SetShadow();

// The order the plots are added determines who's ontop
$graph->add($b1);

// Finally output the  image
$graph->stroke();
