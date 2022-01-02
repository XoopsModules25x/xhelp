<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_scatter.php';

$datay1 = [4, 26, 15, 44];

// Setup the graph
$graph = new Graph(300, 250);
$graph->clearTheme();
$graph->setMarginColor('white');
$graph->setScale('textlin');
$graph->setFrame(false);
$graph->setMargin(30, 5, 25, 50);

// Setup the tab
$graph->tabtitle->set(' Year 2003 ');
$graph->tabtitle->SetFont(FF_ARIAL, FS_BOLD, 13);
$graph->tabtitle->setColor('darkred', '#E1E1FF');

// Enable X-grid as well
$graph->xgrid->Show();

// Use months as X-labels
$graph->xaxis->SetTickLabels($gDateLocale->getShortMonth());

$graph->footer->left->Set('L. footer');
$graph->footer->left->SetFont(FF_ARIAL, FS_NORMAL, 12);
$graph->footer->left->SetColor('darkred');
$graph->footer->center->Set('C. footer');
$graph->footer->center->SetFont(FF_ARIAL, FS_BOLD, 12);
$graph->footer->center->SetColor('darkred');
$graph->footer->right->Set('R. footer');
$graph->footer->right->SetFont(FF_ARIAL, FS_NORMAL, 12);
$graph->footer->right->SetColor('darkred');

// Create the plot
$p1 = new LinePlot($datay1);
$p1->SetColor('navy');

// Use an image of favourite car as marker
$p1->mark->SetType(MARK_IMG, 'saab_95.jpg', 0.5);

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

$graph->stroke();
