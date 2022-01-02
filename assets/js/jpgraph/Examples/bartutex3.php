<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

// Some data
$months = $gDateLocale->getShortMonth();

for ($i = 0; $i < 25; ++$i) {
    $databary[] = mt_rand(1, 50);
    $databarx[] = $months[$i % 12];
}

// New graph with a drop shadow
$graph = new Graph(300, 200, 'auto');
$graph->clearTheme();
$graph->setShadow();

// Use a "text" X-scale
$graph->setScale('textlin');

// Specify X-labels
$graph->xaxis->SetTickLabels($databarx);

// Set title and subtitle
$graph->title->Set('Bar tutorial example 3');

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
