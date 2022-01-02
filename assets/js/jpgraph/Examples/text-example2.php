<?php
// content="text/plain; charset=utf-8"
require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_canvas.php';

// Create the graph.
$graph = new CanvasGraph(350, 200);

$t1 = new Text("a good\nas you can see right now per see\nThis is a text with\nseveral lines\n");
$t1->SetPos(0.05, 100);
$t1->SetFont(FF_FONT1, FS_NORMAL);
$t1->SetBox('white', 'black', true);
$t1->ParagraphAlign('right');
$t1->SetColor('black');
$graph->addText($t1);

$graph->Stroke();
