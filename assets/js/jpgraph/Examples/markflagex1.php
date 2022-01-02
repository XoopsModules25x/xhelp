<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_scatter.php';

$datay = [
    [4, 26, 15, 44],
    [20, 51, 32, 20],
];

// Setup the graph
$graph = new Graph(300, 200);
$graph->clearTheme();
$graph->setMarginColor('white');
$graph->setScale('textlin');
$graph->setFrame(false);
$graph->setMargin(30, 5, 25, 20);

// Enable X-grid as well
$graph->xgrid->Show();

// Use months as X-labels
$graph->xaxis->SetTickLabels($gDateLocale->getShortMonth());

//------------------------
// Create the plots
//------------------------
$p1 = new LinePlot($datay[0]);
$p1->SetColor('navy');

// Use a flag
$p1->mark->SetType(MARK_FLAG1, 197);

// Displayes value on top of marker image
$p1->value->setFormat('%d mil');
$p1->value->show();
$p1->value->setColor('darkred');
$p1->value->setFont(FF_ARIAL, FS_BOLD, 10);
// Increase the margin so that the value is printed avove tje
// img marker
$p1->value->setMargin(14);

// Incent the X-scale so the first and last point doesn't
// fall on the edges
$p1->setCenter();

$graph->add($p1);

//------------
// 2:nd plot
//------------
$p2 = new LinePlot($datay[1]);
$p2->SetColor('navy');

// Use a flag
$p2->mark->SetType(MARK_FLAG1, 'united states');

// Displayes value on top of marker image
$p2->value->setFormat('%d mil');
$p2->value->show();
$p2->value->setColor('darkred');
$p2->value->setFont(FF_ARIAL, FS_BOLD, 10);
// Increase the margin so that the value is printed avove tje
// img marker
$p2->value->setMargin(14);

// Incent the X-scale so the first and last point doesn't
// fall on the edges
$p2->setCenter();
$graph->add($p2);

$graph->stroke();
