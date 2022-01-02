<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

// Some data
$databary = [12, 7, 16, 5, 7, 14, 9, 3];

// New graph with a drop shadow
$graph = new Graph(300, 200);
$graph->clearTheme();
$graph->setShadow();

// Use a "text" X-scale
$graph->setScale('textlin');

// Set title and subtitle
$graph->title->Set('Elementary barplot with a text scale');

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
