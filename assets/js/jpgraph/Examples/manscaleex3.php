<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$ydata = [12, 17, 22, 19, 5, 15];

$graph = new Graph(250, 170);
$graph->setScale('textlin', 3, 35);
$graph->setTickDensity(TICKD_DENSE);
$graph->yscale->SetAutoTicks();

$graph->title->Set('Manual scale, auto ticks');
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$line = new LinePlot($ydata);
$graph->add($line);

// Output graph
$graph->stroke();

?>


