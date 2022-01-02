<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_utils.inc.php';

$f = new FuncGenerator('cos($i)', '$i*$i*$i');
[$xdata, $ydata] = $f->E(-M_PI, M_PI, 25);

$graph = new Graph(350, 430);
$graph->clearTheme();
$graph->setScale('linlin');
$graph->setShadow();
$graph->img->setMargin(50, 50, 60, 40);
$graph->setBox(true, 'black', 2);
$graph->setMarginColor('white');
$graph->setColor('lightyellow');
$graph->setAxisStyle(AXSTYLE_BOXIN);
$graph->xgrid->Show();

//$graph->xaxis->SetLabelFormat('%.0f');

$graph->img->setMargin(50, 50, 60, 40);

$graph->title->Set('Function plot');
$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->subtitle->Set('(BOXIN Axis style)');
$graph->subtitle->SetFont(FF_FONT1, FS_NORMAL);

$lp1 = new LinePlot($ydata, $xdata);
$lp1->SetColor('blue');
$lp1->setWeight(2);

$graph->add($lp1);
$graph->stroke();
