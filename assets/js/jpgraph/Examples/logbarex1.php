<?php
// content="text/plain; charset=utf-8"
// $Id: logbarex1.php,v 1.4 2003/05/30 20:12:43 aditus Exp $
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_log.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

$datay = [4, 13, 30, 28, 12, 45, 30, 12, 55, 3, 0.5];
$datax = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'];

// Create the graph.
$graph = new Graph(400, 220, 'auto');
$graph->clearTheme();
//$graph->img->SetMargin(50,30,50,50);
$graph->setScale('textlog');
//$graph->SetShadow();

// Setup titles for graph and axis
$graph->title->Set('Bar with logarithmic Y-scale');
$graph->title->SetFont(FF_VERDANA, FS_NORMAL, 18);

$graph->xaxis->SetTitle('2002');
$graph->xaxis->title->SetFont(FF_ARIAL, FS_NORMAL, 16);

$graph->yaxis->title->SetFont(FF_ARIAL, FS_NORMAL, 16);
$graph->yaxis->SetTitle('Y-title', 'center');
$graph->yaxis->SetTitleMargin(30);

// Setup month on X-scale
//$graph->xaxis->SetTickLabels($datax);

// Create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillColor('orange');

//You can also set a manual base of the bars
//$bplot->SetYBase(0.001);

/*
$bplot->SetShadow();
$bplot->value->Show();
$bplot->value->SetFont(FF_ARIAL,FS_BOLD);
$bplot->value->SetAngle(45);
$bplot->value->SetColor("black","darkred");
*/

$graph->add($bplot);

$graph->stroke();
