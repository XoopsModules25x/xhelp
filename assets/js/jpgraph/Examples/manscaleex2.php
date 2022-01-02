<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$ydata = [12, 17, 22, 19, 5, 15];

$graph = new Graph(220, 170);
$graph->setScale('textlin', 3, 35);

$graph->title->Set('Manual scale, exact limits');
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$line = new LinePlot($ydata);
$graph->add($line);

// Output graph
$graph->stroke();

?>


