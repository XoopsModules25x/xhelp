<?php

require_once \dirname(__DIR__) . '/jpgraph.php';
require_once \dirname(__DIR__) . '/jpgraph_line.php';

$datay = [0, 3, 5, 12, 15, 18, 22, 36, 37, 41];

// Setup the graph
$graph = new Graph(320, 200);
$graph->title->Set('Education growth');
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 14);
$graph->setScale('intlin');
$graph->setMarginColor('white');
$graph->setBox();
//$graph->img->SetAntialiasing();

$graph->setGridDepth(DEPTH_FRONT);
$graph->ygrid->SetColor('gray@0.7');
$graph->setBackgroundImage('classroom.jpg', BGIMG_FILLPLOT);

// Masking graph
$p1 = new LinePlot($datay);
$p1->SetFillColor('white');
$p1->SetFillFromYMax();
$p1->setWeight(0);
$graph->add($p1);

// Line plot
$p2 = new LinePlot($datay);
$p2->SetColor('black@0.4');
$p2->setWeight(3);
$p2->mark->SetType(MARK_SQUARE);
$p2->mark->SetColor('orange@0.5');
$p2->mark->SetFillColor('orange@0.3');
$graph->add($p2);

// Output line
$graph->stroke();

?>


