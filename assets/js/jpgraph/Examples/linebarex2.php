<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

// Some data

$steps = 100;
for ($i = 0; $i < $steps; ++$i) {
    $datay[$i] = log(pow($i, $i / 10) + 1) * sin($i / 15) + 35;
    $datax[]   = $i;
    if (0 == $i % 10) {
        $databarx[] = $i;
        $databary[] = $datay[$i] / 2;
    }
}

// New graph with a background image and drop shadow
$graph = new Graph(450, 300);
$graph->clearTheme();
$graph->setBackgroundImage('tiger_bkg.png', BGIMG_FILLFRAME);
$graph->setShadow();

// Use an integer X-scale
$graph->setScale('intlin');

// Set title and subtitle
$graph->title->Set('Combined bar and line plot');
$graph->subtitle->Set('("left" aligned bars)');

// Use built in font
$graph->title->SetFont(FF_FONT1, FS_BOLD);

// Make the margin around the plot a little bit bigger
// then default
$graph->img->setMargin(40, 120, 40, 40);

// Slightly adjust the legend from it's default position in the
// top right corner to middle right side
$graph->legend->Pos(0.05, 0.5, 'right', 'center');

// Create a red line plot
$p1 = new LinePlot($datay, $datax);
$p1->SetColor('red');
$p1->setLegend('Status one');
$graph->add($p1);

// Create the bar plot
$b1 = new BarPlot($databary, $databarx);
$b1->setLegend('Status two');
$b1->SetAlign('left');
$b1->SetShadow();
$graph->add($b1);

// Finally output the  image
$graph->stroke();
