<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

//bar1
$data1y = [115, 130, 135, 130, 110, 130, 130, 150, 130, 130, 150, 120];
//bar2
$data2y = [180, 200, 220, 190, 170, 195, 190, 210, 200, 205, 195, 150];
//bar3
$data3y = [220, 230, 210, 175, 185, 195, 200, 230, 200, 195, 180, 130];
$data4y = [40, 45, 70, 80, 50, 75, 70, 70, 80, 75, 80, 50];
$data5y = [20, 20, 25, 22, 30, 25, 35, 30, 27, 25, 25, 45];
//line1
$data6y = [50, 58, 60, 58, 53, 58, 57, 60, 58, 58, 57, 50];
foreach ($data6y as &$y) {
    $y -= 10;
}

// Create the graph. These two calls are always required
$graph = new Graph(750, 320, 'auto');
$graph->setScale('textlin');
$graph->setY2Scale('lin', 0, 90);
$graph->setY2OrderBack(false);

$graph->setMargin(35, 50, 20, 5);

$theme_class = new UniversalTheme();
$graph->setTheme($theme_class);

$graph->yaxis->setTickPositions([0, 50, 100, 150, 200, 250, 300, 350], [25, 75, 125, 175, 275, 325]);
$graph->y2axis->setTickPositions([30, 40, 50, 60, 70, 80, 90]);

$months = $gDateLocale->getShortMonth();
$months = array_merge(array_slice($months, 3, 9), array_slice($months, 0, 3));
$graph->setBox(false);

$graph->ygrid->setFill(false);
$graph->xaxis->setTickLabels(['A', 'B', 'C', 'D']);
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false, false);
// Setup month as labels on the X-axis
$graph->xaxis->setTickLabels($months);

// Create the bar plots
$b1plot = new BarPlot($data1y);
$b2plot = new BarPlot($data2y);

$b3plot = new BarPlot($data3y);
$b4plot = new BarPlot($data4y);
$b5plot = new BarPlot($data5y);

$lplot = new LinePlot($data6y);

// Create the grouped bar plot
$gbbplot = new AccBarPlot([$b3plot, $b4plot, $b5plot]);
$gbplot  = new GroupBarPlot([$b1plot, $b2plot, $gbbplot]);

// ...and add it to the graPH
$graph->add($gbplot);
$graph->addY2($lplot);

$b1plot->setColor('#0000CD');
$b1plot->SetFillColor('#0000CD');
$b1plot->setLegend('Cliants');

$b2plot->setColor('#B0C4DE');
$b2plot->SetFillColor('#B0C4DE');
$b2plot->setLegend('Machines');

$b3plot->setColor('#8B008B');
$b3plot->SetFillColor('#8B008B');
$b3plot->setLegend('First Track');

$b4plot->setColor('#DA70D6');
$b4plot->SetFillColor('#DA70D6');
$b4plot->setLegend('All');

$b5plot->setColor('#9370DB');
$b5plot->SetFillColor('#9370DB');
$b5plot->setLegend('Single Only');

$lplot->SetBarCenter();
$lplot->SetColor('yellow');
$lplot->setLegend('Houses');
$lplot->mark->SetType(MARK_X, '', 1.0);
$lplot->mark->SetWeight(2);
$lplot->mark->SetWidth(8);
$lplot->mark->SetColor('yellow');
$lplot->mark->SetFillColor('yellow');

$graph->legend->SetFrameWeight(1);
$graph->legend->SetColumns(6);
$graph->legend->SetColor('#4E4E4E', '#00A78A');

$band = new PlotBand(VERTICAL, BAND_RDIAG, 11, 'max', 'khaki4');
$band->ShowFrame(true);
$band->SetOrder(DEPTH_BACK);
$graph->add($band);

$graph->title->Set('Combineed Line and Bar plots');

// Display the graph
$graph->stroke();
