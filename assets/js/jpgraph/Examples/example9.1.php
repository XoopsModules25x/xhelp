<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_log.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

$ydata = [11, 3, 8, 42, 5, 1, 9, 13, 5, 7];
$datax = ['Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'aug', 'Sep', 'Oct'];

// Create the graph. These two calls are always required
$graph = new Graph(350, 200);
$graph->clearTheme();
$graph->setScale('textlog');

$graph->img->setMargin(40, 110, 20, 40);
$graph->setShadow();

$graph->ygrid->Show(true, true);
$graph->xgrid->Show(true, false);

// Specify the tick labels
$a = $gDateLocale->getShortMonth();
$graph->xaxis->SetTickLabels($a);
$graph->xaxis->SetTextLabelInterval(2);

// Create the linear plot
$lineplot = new LinePlot($ydata);

// Add the plot to the graph
$graph->add($lineplot);

$graph->title->Set('Examples 9');
$graph->xaxis->title->Set('X-title');
$graph->yaxis->title->Set('Y-title');

$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

$lineplot->SetColor('blue');
$lineplot->setWeight(2);

$graph->yaxis->SetColor('blue');

$lineplot->setLegend('Plot 1');

$graph->legend->Pos(0.05, 0.5, 'right', 'center');

// Display the graph
$graph->stroke();
