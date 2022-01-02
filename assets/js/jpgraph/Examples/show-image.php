<?php
$target = urldecode($_GET['target']); ?>
<!DOCTYPE html>
<html>
<head>
    <title> Image <?php echo basename($target); ?></title>
</head>
<body>
<img src="<?php echo basename($target); ?>" border=0 alt="<?php echo basename($target); ?>" align="left">
</body>
</html>
