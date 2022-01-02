<?php
// content="text/plain; charset=utf-8"

require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

// create the graph
$graph = new Graph(400, 250);
$graph->clearTheme();

$ydata = [5, 10, 15, 20, 15, 10];

$graph->setScale('textlin');
$graph->setShadow(true);
$graph->setMarginColor('antiquewhite');
$graph->img->setMargin(60, 40, 40, 50);
$graph->img->SetTransparent('white');
$graph->xaxis->SetFont(FF_FONT1);
$graph->xaxis->setTextTickInterval(1);
$graph->xaxis->SetTextLabelInterval(1);
$graph->legend->SetFillColor('antiquewhite');
$graph->legend->SetShadow(true);
$graph->legend->SetLayout(LEGEND_VERT);
$graph->legend->Pos(0.02, 0.01);
$graph->title->Set('Step Styled Example');
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$lineplot = new LinePlot($ydata);
$lineplot->SetColor('black');
$lineplot->SetFillColor('gray7');
$lineplot->SetStepStyle();
$lineplot->setLegend(' 2002 ');

// add plot to the graph
$graph->add($lineplot);
$graph->ygrid->show(false, false);

// display graph
$graph->stroke();
