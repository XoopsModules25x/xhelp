<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

// Some data
$ydata = [11, 3, 8, 12, 5, 1, 9, 13, 5, 7];

// Create the graph. These two calls are always required
$graph = new Graph(350, 250);
$graph->clearTheme();
$graph->setScale('textlin');
$graph->setMargin(40, 40, 50, 50);

// Setup the grid and plotarea box
$graph->ygrid->SetLineStyle('dashed');
$graph->ygrid->setColor('darkgray');
$graph->setBox(true);

// Steup graph titles
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 12);
$graph->title->Set('Using background image');
$graph->subtitle->SetFont(FF_COURIER, FS_BOLD, 11);
$graph->subtitle->Set('"BGIMG_FILLFRAME"');
$graph->subtitle->SetColor('darkred');

// Add background with 25% mix
$graph->setBackgroundImage('heat1.jpg', BGIMG_FILLFRAME);
$graph->setBackgroundImageMix(25);

// Create the linear plot
$lineplot = new LinePlot($ydata);
$lineplot->SetColor('blue');

// Add the plot to the graph
$graph->add($lineplot);

// Display the graph
$graph->stroke();
