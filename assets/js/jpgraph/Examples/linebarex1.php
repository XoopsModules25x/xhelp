<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

$month = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'Maj',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Okt',
    'Nov',
    'Dec',
];

// Create datapoints where every point
$steps = 100;
for ($i = 0; $i < $steps; ++$i) {
    $datay[$i]  = log(pow($i, $i / 10) + 1) * sin($i / 15) + 35;
    $databarx[] = sprintf('198%d %s', floor($i / 12), $month[$i % 12]);

    // Simulate an accumulated value for every 5:th data point
    if (0 == $i % 6) {
        $databary[] = abs(25 * sin($i) + 5);
    } else {
        $databary[] = 0;
    }
}

// New graph with a background image and drop shadow
$graph = new Graph(450, 300);
$graph->clearTheme();
$graph->setBackgroundImage('tiger_bkg.png', BGIMG_FILLFRAME);
$graph->setShadow();

// Use an integer X-scale
$graph->setScale('textlin');

// Set title and subtitle
$graph->title->Set('Combined bar and line plot');
$graph->subtitle->Set("100 data points, X-Scale: 'text'");

// Use built in font
$graph->title->SetFont(FF_FONT1, FS_BOLD);

// Make the margin around the plot a little bit bigger
// then default
$graph->img->setMargin(40, 140, 40, 80);

// Slightly adjust the legend from it's default position in the
// top right corner to middle right side
$graph->legend->Pos(0.05, 0.5, 'right', 'center');

// Display every 10:th datalabel
$graph->xaxis->SetTextTickInterval(6);
$graph->xaxis->SetTextLabelInterval(2);
$graph->xaxis->SetTickLabels($databarx);
$graph->xaxis->SetLabelAngle(90);

// Create a red line plot
$p1 = new LinePlot($datay);
$p1->SetColor('red');
$p1->setLegend('Pressure');

// Create the bar plot
$b1 = new BarPlot($databary);
$b1->setLegend('Temperature');
$b1->SetAbsWidth(6);
$b1->SetShadow();

// The order the plots are added determines who's ontop
$graph->add($p1);
$graph->add($b1);

// Finally output the  image
$graph->stroke();
