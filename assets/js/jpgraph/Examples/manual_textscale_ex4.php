<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_line.php';

define('DATAPERMONTH', 40);

// Some data
$m = $gDateLocale->getShortMonth();
$k = 0;
for ($i = 0; $i < 480; ++$i) {
    $datay[$i] = mt_rand(1, 40);
    if (0 === $i % DATAPERMONTH) {
        $months[$i] = $m[(int)($i / DATAPERMONTH)];
    } else {
        $months[$i] = 'xx';
    }
}

// New graph with a drop shadow
$graph = new Graph(400, 200);
//$graph->SetShadow();

// Use a "text" X-scale
$graph->setScale('textlin');

// Specify X-labels
$graph->xaxis->SetTickLabels($months);
$graph->xaxis->SetTextTickInterval(DATAPERMONTH, 0);
$graph->xaxis->SetTextLabelInterval(2);

// Set title and subtitle
$graph->title->Set('Textscale with tickinterval=2');

// Use built in font
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$graph->setBox(true, 'red');

// Create the bar plot
$lp1 = new LinePlot($datay);
$lp1->setLegend('Temperature');

// The order the plots are added determines who's ontop
$graph->add($lp1);

// Finally output the  image
$graph->stroke();

?>


