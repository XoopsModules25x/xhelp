<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_scatter.php';

// Make a circle with a scatterplot
$steps = 16;
for ($i = 0; $i < $steps; ++$i) {
    $a         = 2 * M_PI / $steps * $i;
    $datax[$i] = cos($a);
    $datay[$i] = sin($a);
}

$graph = new Graph(300, 200);
$graph->clearTheme();
$graph->setScale('linlin');

$graph->img->setMargin(40, 40, 40, 40);

$graph->setShadow();
$graph->title->Set('Linked scatter plot');
$graph->title->SetFont(FF_FONT1, FS_BOLD);

// 10% top and bottom grace
$graph->yscale->SetGrace(5, 5);
$graph->xscale->SetGrace(1, 1);

$sp1 = new ScatterPlot($datay, $datax);
$sp1->mark->SetType(MARK_FILLEDCIRCLE);
$sp1->mark->SetFillColor('red');
$sp1->setColor('blue');

//$sp1->SetWeight(3);
$sp1->mark->SetWidth(4);
$sp1->SetLinkPoints();

$graph->add($sp1);

$graph->stroke();
