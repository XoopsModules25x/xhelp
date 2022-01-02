<?php
// content="text/plain; charset=utf-8"
// $Id: groupbarex1.php,v 1.2 2002/07/11 23:27:28 aditus Exp $
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

$datay1 = [35, 160, 0, 0, 0, 0];
$datay2 = [35, 190, 190, 190, 190, 190];
$datay3 = [20, 70, 70, 140, 230, 260];

$graph = new Graph(450, 200, 'auto');
$graph->clearTheme();
$graph->setScale('textlin');
$graph->setShadow();
$graph->img->setMargin(40, 30, 40, 40);
$graph->xaxis->SetTickLabels($gDateLocale->getShortMonth());

$graph->xaxis->title->Set('Year 2002');
$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

$graph->title->Set('Group bar plot');
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$bplot1 = new BarPlot($datay1);
$bplot2 = new BarPlot($datay2);
$bplot3 = new BarPlot($datay3);

$bplot1->SetFillColor('orange');
$bplot2->SetFillColor('brown');
$bplot3->SetFillColor('darkgreen');

$bplot1->SetShadow();
$bplot2->SetShadow();
$bplot3->SetShadow();

$bplot1->SetShadow();
$bplot2->SetShadow();
$bplot3->SetShadow();

$gbarplot = new GroupBarPlot([$bplot1, $bplot2, $bplot3]);
$gbarplot->SetWidth(0.6);
$graph->add($gbarplot);

$graph->stroke();
