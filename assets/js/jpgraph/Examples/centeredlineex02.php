<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_error.php';

//$datax = array(3.5,3.7,3,4,6.2,6,3.5,8,14,8,11.1,13.7);
$datay = [1.23, 1.9, 1.6, 3.1, 3.4, 2.8, 2.1, 1.9];
$graph = new Graph(300, 200);
$graph->img->setMargin(40, 40, 40, 40);
$graph->img->SetAntiAliasing();
$graph->setScale('textlin');
$graph->setShadow();
$graph->title->Set('Example of filled line centered plot');
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$p1 = new LinePlot($datay);
$p1->SetFillColor('green');
$p1->mark->SetType(MARK_FILLEDCIRCLE);
$p1->mark->SetFillColor('red');
$p1->mark->SetWidth(4);
$p1->SetColor('blue');
$p1->setCenter();
$graph->add($p1);

$graph->stroke();

?>


