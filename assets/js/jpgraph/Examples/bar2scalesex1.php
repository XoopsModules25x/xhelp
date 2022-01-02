<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

$datay    = [20, 30, 50, 80];
$datay2   = [430, 645, 223, 690];
$datazero = [0, 0, 0, 0];

// Create the graph.
$graph = new Graph(450, 200);
$graph->clearTheme();
$graph->title->Set('Example with 2 scale bars');

// Setup Y and Y2 scales with some "grace"
$graph->setScale('textlin');
$graph->setY2Scale('lin');
$graph->yaxis->scale->SetGrace(30);
$graph->y2axis->scale->SetGrace(30);

//$graph->ygrid->Show(true,true);
$graph->ygrid->SetColor('gray', 'lightgray@0.5');

// Setup graph colors
$graph->setMarginColor('white');
$graph->y2axis->SetColor('darkred');

// Create the "dummy" 0 bplot
$bplotzero = new BarPlot($datazero);

// Create the "Y" axis group
$ybplot1 = new BarPlot($datay);
$ybplot1->value->show();
$ybplot = new GroupBarPlot([$ybplot1, $bplotzero]);

// Create the "Y2" axis group
$ybplot2 = new BarPlot($datay2);
$ybplot2->value->show();
$ybplot2->value->setColor('darkred');
$ybplot2->SetFillColor('darkred');
$y2bplot = new GroupBarPlot([$bplotzero, $ybplot2]);

// Add the grouped bar plots to the graph
$graph->add($ybplot);
$graph->addY2($y2bplot);

// .. and finally stroke the image back to browser
$graph->stroke();
