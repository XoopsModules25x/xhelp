<?php
// content="text/plain; charset=utf-8"

require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$ydata = [12, 15, 22, 19, 5];

$graph = new Graph(400, 200);
$graph->clearTheme();
$graph->img->setMargin(40, 80, 40, 40);
$graph->setScale('textlin');
$graph->setShadow();

$graph->title->Set('Center the line points in bars');

$line = new LinePlot($ydata);
$line->SetBarCenter();
$line->setWeight(2);

$bar  = new BarPlot($ydata);
$bar2 = new BarPlot($ydata);
$bar2->SetFillColor('red');

$gbar = new GroupbarPlot([$bar, $bar2]);

$graph->add($gbar);
$graph->add($line);

// Output line
$graph->stroke();
