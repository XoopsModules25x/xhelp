<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_log.php';

// Matrix size rxr
$r = 10;

// Max Interpolation factor
$f = 5;

for ($i = 1; $i <= $f; ++$i) {
    $xdata[] = $i;
    $ydata[] = pow($r * pow(2, $i - 1) - (pow(2, $i) - 1), 2);
}

$graph = new Graph(400, 240);
$graph->setScale('intint');
$graph->setMargin(50, 50, 20, 30);
$graph->setFrame(false);
$graph->setBox(true, 'black', 2);
$graph->setMarginColor('white');
$graph->setColor('lightyellow@0.7');

$graph->title->Set('Interpolation growth for size 10x10');
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$graph->xaxis->SetTitle('Interpolation factor', 'center');
$graph->xaxis->SetTitleMargin(10);

$graph->setAxisStyle(AXSTYLE_YBOXIN);
$graph->xgrid->Show();

$lp1 = new LinePlot($ydata, $xdata);
$lp1->SetColor('darkred');
$lp1->setWeight(3);
$graph->add($lp1);

$graph->stroke();
?>


