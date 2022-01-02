<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

// Callback to negate the argument
function _cb_negate($aVal)
{
    return round(-$aVal);
}

// A fake depth curve
$ydata = [0, 1, 4, 5, 8, 9, 10, 14, 16, 16, 16, 18, 20, 20, 20, 22, 22.5, 22, 19, 19, 15, 15, 15, 15, 10, 10, 10, 6, 5, 5, 5, 4, 4, 2, 1, 0];

$n      = count($ydata);
$y2data = [];
for ($i = 0; $i < $n; ++$i) {
    $y2data[] = $ydata[$i] + 10;
}

// Negate all data
$n = count($ydata);
for ($i = 0; $i < $n; ++$i) {
    $ydata[$i]  = round(-$ydata[$i]);
    $y2data[$i] = round(-$y2data[$i]);
}

// Basic graph setup
$graph = new Graph(400, 300);
$graph->clearTheme();
$graph->setScale('linlin');
$graph->setY2Scale('lin');
$graph->setMargin(50, 50, 60, 40);
$graph->setMarginColor('darkblue');
$graph->setColor('darkblue');

// Setup titles
$graph->title->Set('Inverting both Y-axis');
$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->title->SetColor('white');

$graph->subtitle->Set('(Negated Y & Y2 axis)');
$graph->subtitle->SetFont(FF_FONT1, FS_NORMAL);
$graph->subtitle->SetColor('white');

// Setup axis
$graph->yaxis->SetLabelFormatCallback('_cb_negate');
$graph->xaxis->SetColor('lightblue', 'white');
$graph->yaxis->SetColor('lightblue', 'white');
$graph->ygrid->SetColor('blue');

// Setup Y2 axis
$graph->y2axis->SetLabelFormatCallback('_cb_negate');
$graph->y2axis->SetColor('darkred', 'white');
$graph->y2scale->SetAutoMax(0); // To make sure it starts with 0

// Setup plot 1
$lp1 = new LinePlot($ydata);
$lp1->SetColor('yellow');
$lp1->setWeight(2);
$graph->add($lp1);

// Setup plot 2
$lp2 = new LinePlot($y2data);
$lp2->SetColor('darkred');
$lp2->setWeight(2);
$graph->addY2($lp2);

$graph->stroke();
