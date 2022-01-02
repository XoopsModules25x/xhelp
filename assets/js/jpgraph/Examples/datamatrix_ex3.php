<?php

require_once __DIR__ . '/jpgraph/datamatrix/datamatrix.inc.php';

$data = '123456';

// Create and set parameters for the encoder
$encoder = DatamatrixFactory::Create(DMAT_44x44);
$encoder->SetEncoding(ENCODING_BASE256);

// Create the image backend (default)
$backend = DatamatrixBackendFactory::Create($encoder);
$backend->SetModuleWidth(3);

try {
    $backend->Stroke($data);
} catch (\Throwable $e) {
    $errstr = $e->getMessage();
    echo "Datamatrix error message: $errstr\n";
}
