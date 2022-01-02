<?php

require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_bar.php';

$data1y = [47, 80, 40, 116];
$graph  = new Graph(400, 300, 'auto');
$graph->setScale('textlin');

$theme_class = new AquaTheme();
$graph->setTheme($theme_class);

// after setting theme, you can change details as you want
$graph->setFrame(true, 'lightgray');                        // set frame visible

$graph->xaxis->SetTickLabels(['A', 'B', 'C', 'D']); // change xaxis lagels
$graph->title->Set('Theme Example');                    // add title

// add barplot
$bplot = new BarPlot($data1y);
$graph->add($bplot);
$graph->setColor('#cc1111');    // you can change color only after calling Add()

$graph->stroke();
