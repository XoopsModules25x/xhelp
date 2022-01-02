<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';
require_once __DIR__ . '/jpgraph/jpgraph_scatter.php';

$datay1 = [15, 21, 24, 10, 37, 29, 47];
$datay2 = [8, 6, 11, 26, 10, 4, 2];

// Setup the graph
$graph = new Graph(300, 250);

$graph->setScale('textlin', 0, 50);

//$theme_class=new DefaultTheme;
//$graph->SetTheme($theme_class);

$graph->title->Set('Filled Area');

$graph->setBox(false);
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false, false);
$graph->yaxis->HideZeroLabel();

$graph->xaxis->SetTickLabels(['A', 'B', 'C', 'D', 'E', 'F', 'G']);

// Create the plot
$p1 = new LinePlot($datay1);
$graph->add($p1);

$p2 = new LinePlot($datay2);
$graph->add($p2);

// Use an image of favourite car as marker
$p1->mark->SetType(MARK_IMG, 'rose.gif', 1.0);
$p1->setLegend('rose');
$p1->SetColor('#CD5C5C');

$p2->mark->SetType(MARK_IMG, 'sunflower.gif', 1.0);
$p2->setLegend('sunflower');
$p2->SetColor('#CD5C5C');

$graph->stroke();

?>


