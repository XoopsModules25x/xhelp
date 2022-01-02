<?php
// content="text/plain; charset=utf-8"

require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';
require_once __DIR__ . '/jpgraph/jpgraph_flags.php';

// Some data
$datay1 = [140, 110, 50];
$datay2 = [35, 90, 190];
$datay3 = [20, 60, 70];

// Create the basic graph
$graph = new Graph(300, 200);
$graph->clearTheme();
$graph->setScale('textlin');
$graph->setMargin(40, 20, 20, 40);
$graph->setMarginColor('white:0.9');
$graph->setColor('white');
$graph->setShadow();

// Apply a perspective transformation at the end
$graph->set3DPerspective(SKEW3D_DOWN, 100, 180);

// Adjust the position of the legend box
$graph->legend->Pos(0.03, 0.10);

// Adjust the color for theshadow of the legend
$graph->legend->SetShadow('darkgray@0.5');
$graph->legend->SetFillColor('lightblue@0.1');
$graph->legend->Hide();

// Get localised version of the month names
$graph->xaxis->SetTickLabels($gDateLocale->getShortMonth());

$graph->setBackgroundCountryFlag('mais', BGIMG_COPY, 50);

// Set axis titles and fonts
$graph->xaxis->title->Set('Year 2002');
$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->title->SetColor('white');

$graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->SetColor('navy');

$graph->yaxis->SetFont(FF_FONT1, FS_BOLD);
$graph->yaxis->SetColor('navy');

//$graph->ygrid->Show(false);
$graph->ygrid->SetColor('white@0.5');

// Setup graph title
$graph->title->Set('Using a country flag background');

// Some extra margin (from the top)
$graph->title->SetMargin(3);
$graph->title->SetFont(FF_ARIAL, FS_NORMAL, 12);

// Create the three var series we will combine
$bplot1 = new BarPlot($datay1);
$bplot2 = new BarPlot($datay2);
$bplot3 = new BarPlot($datay3);

// Setup the colors with 40% transparency (alpha channel)
$bplot1->SetFillColor('yellow@0.4');
$bplot2->SetFillColor('red@0.4');
$bplot3->SetFillColor('darkgreen@0.4');

// Setup legends
$bplot1->setLegend('Label 1');
$bplot2->setLegend('Label 2');
$bplot3->setLegend('Label 3');

// Setup each bar with a shadow of 50% transparency
$bplot1->SetShadow('black@0.4');
$bplot2->SetShadow('black@0.4');
$bplot3->SetShadow('black@0.4');

$gbarplot = new GroupBarPlot([$bplot1, $bplot2, $bplot3]);
$gbarplot->SetWidth(0.6);
$graph->add($gbarplot);

$graph->stroke();
