<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

// Some data
for ($i = 0; $i < 12; ++$i) {
    $databary[$i] = mt_rand(1, 20);
}
$months = $gDateLocale->getShortMonth();

// New graph with a drop shadow
$graph = new Graph(300, 200);
$graph->clearTheme();
$graph->setShadow();

// Use a "text" X-scale
$graph->setScale('textlin');

// Specify X-labels
$graph->xaxis->SetTickLabels($months);
$graph->xaxis->SetTextTickInterval(2, 0);

// Set title and subtitle
$graph->title->Set('Textscale with tickinterval=2');

// Use built in font
$graph->title->SetFont(FF_FONT1, FS_BOLD);

// Create the bar plot
$b1 = new BarPlot($databary);
$b1->setLegend('Temperature');

// The order the plots are added determines who's ontop
$graph->add($b1);

// Finally output the  image
$graph->stroke();
