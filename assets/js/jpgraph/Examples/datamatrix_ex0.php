<?php

require_once __DIR__ . '/jpgraph/datamatrix/datamatrix.inc.php';

$data    = 'The first datamatrix';
$encoder = DatamatrixFactory::Create();
$encoder->SetEncoding(ENCODING_ASCII);
$backend = DatamatrixBackendFactory::Create($encoder);

// We increase the module width to 3 pixels
$backend->SetModuleWidth(3);

try {
    $backend->Stroke($data);
} catch (\Throwable $e) {
    echo 'Datamatrix error: ' . $e->getMessage() . "\n";
    exit(1);
}
