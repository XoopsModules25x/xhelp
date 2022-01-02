<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_utils.inc.php';

$f = new FuncGenerator('cos($i)', '$i*$i*$i');
[$xdata, $ydata] = $f->E(-M_PI, M_PI, 25);

$graph = new Graph(380, 450);
$graph->clearTheme();
$graph->setScale('linlin');
$graph->setShadow();
$graph->img->setMargin(50, 50, 60, 40);
$graph->setBox(true, 'black', 2);
$graph->setMarginColor('white');
$graph->setColor('lightyellow');
$graph->setAxisStyle(AXSTYLE_SIMPLE);

//$graph->xaxis->SetLabelFormat('%.1f');

$graph->title->Set('Function plot with marker');
$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->subtitle->Set('(BOXOUT Axis style)');
$graph->subtitle->SetFont(FF_FONT1, FS_NORMAL);

$lp1 = new LinePlot($ydata, $xdata);
$lp1->mark->SetType(MARK_FILLEDCIRCLE);
$lp1->mark->SetFillColor('red');
$lp1->SetColor('blue');

$graph->add($lp1);
$graph->stroke();
