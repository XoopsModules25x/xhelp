<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_utils.inc.php';

$f = new FuncGenerator('cos($x)*$x');
[$xdata, $ydata] = $f->E(-1.2 * M_PI, 1.2 * M_PI);

$f = new FuncGenerator('$x*$x');
[$x2data, $y2data] = $f->E(-2, 2);

// Setup the basic graph
$graph = new Graph(450, 350);
$graph->clearTheme();
$graph->setScale('linlin');
$graph->setShadow();
$graph->img->setMargin(50, 50, 60, 40);
$graph->setBox(true, 'black', 2);
$graph->setMarginColor('white');
$graph->setColor('lightyellow');

// ... and titles
$graph->title->Set('Example of Function plot');
$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->subtitle->Set("(With some more advanced axis formatting\nHiding first and last label)");
$graph->subtitle->SetFont(FF_FONT1, FS_NORMAL);
$graph->xgrid->Show();

$graph->yaxis->SetPos(0);
$graph->yaxis->SetWeight(2);
$graph->yaxis->HideZeroLabel();
$graph->yaxis->SetFont(FF_FONT1, FS_BOLD);
$graph->yaxis->SetColor('black', 'darkblue');
$graph->yaxis->HideTicks(true, false);
$graph->yaxis->HideFirstLastLabel();

$graph->xaxis->SetWeight(2);
$graph->xaxis->HideZeroLabel();
$graph->xaxis->HideFirstLastLabel();
$graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->SetColor('black', 'darkblue');

$lp1 = new LinePlot($ydata, $xdata);
$lp1->SetColor('blue');
$lp1->setWeight(2);

$lp2 = new LinePlot($y2data, $x2data);
[$xm, $ym] = $lp2->max();
$lp2->SetColor('red');
$lp2->setWeight(2);

$graph->add($lp1);
$graph->add($lp2);
$graph->stroke();
