<?php

require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_canvas.php';
require_once __DIR__ . '/jpgraph/jpgraph_table.php';

// Create a canvas graph where the table can be added
$graph = new CanvasGraph(70, 50);

// Setup the basic table
$data  = [[1, 2, 3, 4], [5, 6, 7, 8]];
$table = new GTextTable();
$table->Set($data);

// Merge all cells in row 0
$table->MergeRow(0);

// Add table to graph
$graph->add($table);

// ... and send back the table to the client
$graph->Stroke();

?>

