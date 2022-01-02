<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_scatter.php';

$datay1 = [4, 26, 15, 44];

// Setup the graph
$graph = new Graph(300, 200);
$graph->setMarginColor('white');
$graph->setScale('textlin');
$graph->setFrame(false);
$graph->setMargin(30, 5, 25, 20);

// Setup the tab
$graph->tabtitle->set(' Year 2003 ');
$graph->tabtitle->SetFont(FF_ARIAL, FS_BOLD, 13);
$graph->tabtitle->setColor('darkred', '#E1E1FF');

// Enable X-grid as well
$graph->xgrid->Show();

// Use months as X-labels
$graph->xaxis->SetTickLabels($gDateLocale->getShortMonth());

// Create the plot
$p1 = new LinePlot($datay1);
$p1->SetColor('navy');

$p1->setCSIMTargets(['#1', '#2', '#3', '#4', '#5']);

// Use an image of favourite car as
$p1->mark->SetType(MARK_IMG, 'saab_95.jpg', 0.5);
//$p1->mark->SetType(MARK_SQUARE);

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

$graph->strokeCSIM();

?>


