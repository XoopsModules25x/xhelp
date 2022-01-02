<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_utils.inc.php';

$f = new FuncGenerator('cos($i)', '$i*$i*$i');
[$xdata, $ydata] = $f->E(-M_PI, M_PI, 25);

$graph = new Graph(300, 200);
$graph->clearTheme();
$graph->setScale('linlin');
$graph->setMargin(50, 50, 20, 30);
$graph->setFrame(false);
$graph->setBox(true, 'black', 2);
$graph->setMarginColor('white');
$graph->setColor('lightyellow');

$graph->title->Set('Duplicating Y-axis');
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$graph->setAxisStyle(AXSTYLE_YBOXIN);
$graph->xgrid->Show();

$lp1 = new LinePlot($ydata, $xdata);
$lp1->SetColor('blue');
$lp1->setWeight(2);
$graph->add($lp1);

$graph->stroke();
