<?php
// $Id: upgradeProgress.php,v 1.2 2005/12/01 22:36:21 ackbarr Exp $
include('../../../include/cp_header.php');
include_once('admin_header.php');

echo "<html>
      <head>
          <title>"._AM_XHELP_UPDATE_DB."</title>
      </head>";
echo "<table width='95%' border='0'>";
echo "<tr><th>"._AM_XHELP_UPDATE_DB."</th></tr>";
echo "<tr><td><img src='".XHELP_IMAGE_URL."/progress.gif' /></td></tr>";
echo "</table>";
echo "</html>";
?>