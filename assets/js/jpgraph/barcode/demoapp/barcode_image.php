<?php

require_once __DIR__ . '/jpgraph/jpgraph.php';
require_once __DIR__ . '/jpgraph/jpgraph_canvas.php';
require_once __DIR__ . '/jpgraph/jpgraph_barcode.php';

$params = [
    ['code', 1],
    ['data', ''],
    ['modwidth', 1],
    ['info', false],
    ['notext', false],
    ['checksum', false],
    ['showframe', false],
    ['vertical', false],
    ['backend', 'IMAGE'],
    ['file', ''],
    ['scale', 1],
    ['height', 70],
    ['pswidth', ''],
];

$n = count($params);
for ($i = 0; $i < $n; ++$i) {
    $v = $params[$i][0];
    if (empty($_GET[$params[$i][0]])) {
        $$v = $params[$i][1];
    } else {
        $$v = $_GET[$params[$i][0]];
    }
}

if ($modwidth < 1 || $modwidth > 5) {
    echo '<h4>Module width must be between 1 and 5 pixels</h4>';
} elseif ('' === $data) {
    echo "<h3>Please enter data to be encoded, select symbology and press 'Ok'.</h3>";
    echo '<i>Note: Data must be valid for the choosen encoding.</i>';
} elseif (-1 == $code) {
    echo '<h4>No code symbology selected.</h4>';
} elseif ($height < 10 || $height > 500) {
    echo '<h4> Height must be in range [10, 500]</h4>';
} elseif ($scale < 0.1 || $scale > 15) {
    echo '<h4> Scale must be in range [0.1, 15]</h4>';
} else {
    if (20 == $code) {
        $encoder = BarcodeFactory::Create(6);
        $encoder->UseExtended();
    } else {
        $encoder = BarcodeFactory::Create($code);
    }
    $b = 'EPS' == $backend ? 'PS' : $backend;
    $b = 'IMAGE' == mb_substr($backend, 0, 5) ? 'IMAGE' : $b;
    $e = BackendFactory::Create($b, $encoder);
    if ('IMAGE' == mb_substr($backend, 0, 5)) {
        if ('J' == mb_substr($backend, 5, 1)) {
            $e->SetImgFormat('JPEG');
        }
    }
    if ($e) {
        if ('EPS' == $backend) {
            $e->SetEPS();
        }
        if ('' != $pswidth) {
            $modwidth = $pswidth;
        }
        $e->SetModuleWidth($modwidth);
        $e->AddChecksum($checksum);
        $e->NoText($notext);
        $e->setScale($scale);
        $e->SetVertical($vertical);
        $e->ShowFrame($showframe);
        $e->SetHeight($height);
        $r = $e->Stroke($data, $file, $info, $info);
        if ($r) {
            echo nl2br(htmlspecialchars($r, ENT_QUOTES | ENT_HTML5));
        }
        if ('' != $file) {
            echo "<p>Wrote file $file.";
        }
    } else {
        echo "<h3>Can't create choosen backend: $backend.</h3>";
    }
}
