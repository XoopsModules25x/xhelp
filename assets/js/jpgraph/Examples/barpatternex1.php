<?php
// content="text/plain; charset=utf-8"

require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$datay = [2, 3, 5, 8.5, 11.5, 6, 3];

// Create the graph.
$graph = new Graph(350, 300);
$graph->clearTheme();

$graph->setScale('textlin');

$graph->setMarginColor('navy:1.9');
$graph->setBox();

$graph->title->Set('Bar Pattern');
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 20);

$graph->setTitleBackground('lightblue:1.3', TITLEBKG_STYLE2, TITLEBKG_FRAME_BEVEL);
$graph->setTitleBackgroundFillStyle(TITLEBKG_FILLSTYLE_HSTRIPED, 'lightblue', 'blue');

// Create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillColor('darkorange');
$bplot->SetWidth(0.6);

$bplot->SetPattern(PATTERN_CROSS1, 'navy');

$graph->add($bplot);

$graph->stroke();
