<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$ydata = [12, 17, 22, 19, 5, 15];

$graph = new Graph(270, 170);
$graph->clearTheme();
$graph->setMargin(30, 90, 30, 30);
$graph->setScale('textlin');

$graph->img->SetAngle(45);
$graph->img->SetCenter(floor(270 / 2), floor(170 / 2));

$line = new LinePlot($ydata);
$line->setLegend('2002');
$line->SetColor('darkred');
$line->setWeight(2);
$graph->add($line);

// Output graph
$graph->stroke();
