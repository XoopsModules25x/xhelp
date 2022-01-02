<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$n = 8;
for ($i = 0; $i < $n; ++$i) {
    $datay[$i]  = mt_rand(1, 10);
    $datay2[$i] = mt_rand(10, 55);
    $datay3[$i] = mt_rand(200, 600);
    $datay4[$i] = mt_rand(500, 800);
}

// Setup the graph
$graph = new Graph(450, 250);
$graph->clearTheme();
$graph->setMargin(40, 150, 40, 30);
$graph->setMarginColor('white');

$graph->setScale('intlin');
$graph->title->Set('Using multiple Y-axis');
$graph->title->SetFont(FF_ARIAL, FS_NORMAL, 14);

$graph->setYScale(0, 'lin');
$graph->setYScale(1, 'lin');
$graph->setYScale(2, 'lin');

$p1 = new LinePlot($datay);
$graph->add($p1);

$p2 = new LinePlot($datay2);
$p2->SetColor('teal');
$graph->addY(0, $p2);
$graph->ynaxis[0]->SetColor('teal');

$p3 = new LinePlot($datay3);
$p3->SetColor('red');
$graph->addY(1, $p3);
$graph->ynaxis[1]->SetColor('red');

$p4 = new LinePlot($datay4);
$p4->SetColor('blue');
$graph->addY(2, $p4);
$graph->ynaxis[2]->SetColor('blue');

// Output line
$graph->stroke();
