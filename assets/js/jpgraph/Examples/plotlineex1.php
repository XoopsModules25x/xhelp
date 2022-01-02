<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_plotline.php';

$datay = [2, 3, 5, 8.5, 11.5, 6, 3];

// Create the graph.
$graph = new Graph(460, 400, 'auto');
$graph->clearTheme();
$graph->setScale('textlin');
$graph->setMargin(40, 20, 50, 70);

$graph->legend->SetPos(0.5, 0.97, 'center', 'bottom');

$graph->title->Set('Plot line legend');
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 14);

$graph->setTitleBackground('lightblue:1.3', TITLEBKG_STYLE2, TITLEBKG_FRAME_BEVEL);
$graph->setTitleBackgroundFillStyle(TITLEBKG_FILLSTYLE_HSTRIPED, 'lightblue', 'navy');

// Create a bar pot
$bplot = new BarPlot($datay);
$bplot->value->show();
$bplot->value->setFont(FF_VERDANA, FS_BOLD, 8);
$bplot->SetValuePos('top');
$bplot->setLegend('Bar Legend');
$graph->add($bplot);

$pline = new PlotLine(HORIZONTAL, 8, 'red', 2);
$pline->SetLegend('Line Legend');
$graph->legend->SetColumns(10);
$graph->add($pline);

$graph->stroke();
