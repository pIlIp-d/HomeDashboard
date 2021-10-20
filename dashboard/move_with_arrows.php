<!DOCTYPE HTML>
<html>
  <link rel="stylesheet" href="style/move_with_arrows.css">
<body>
<?php
    $id = 0;
    $name = "";
    if (isset($_GET["id"])) {
      $id = $_GET["id"];
    }
    if (isset($_GET["name"])) {
      $name = $_GET["name"];
    }
  ?>
<div id="button_id" hidden><?php echo $id; ?></div>
 <span id="name" style="font-family: Arial;z-index: -100" ><?php echo $id; echo $name; ?></span>
<div class="grid-container">
<!-- 
  buttons return their ID(Number) + their ID(name) through postMessage to dashboard 
  the buttons are from font-awesome 
-->
  <!-- up -->
  <img src="/HomeDashboard/images/arrow_up.svg" class="grid-item button_up" aria-hidden="true" id='<?php echo $id; ?>' onclick="window.parent.postMessage('<?php echo $id; ?> click_up', 'http://'+location.host+'/HomeDashboard/dashboard.php')">
  <!-- down -->
  <img src="/HomeDashboard/images/arrow_up.svg" class="grid-item button_down" id='<?php echo $id; ?>' onclick="window.parent.postMessage('<?php echo $id; ?> click_down', 'http://'+location.host+'/HomeDashboard/dashboard.php')">
  <!-- left -->
  <img src="/HomeDashboard/images/arrow_up.svg" class="grid-item button_left" id='<?php echo $id; ?>' onclick="window.parent.postMessage('<?php echo $id; ?> click_left', 'http://'+location.host+'/HomeDashboard/dashboard.php')">
  <!-- right -->
  <img src="/HomeDashboard/images/arrow_up.svg" class="grid-item button_right" id='<?php echo $id; ?>' onclick="window.parent.postMessage('<?php echo $id; ?> click_right', 'http://'+location.host+'/HomeDashboard/dashboard.php')">
  <!-- delete -->
  <img src="/HomeDashboard/images/btn_close.svg" class="grid-item button_delete" id='<?php echo $id; ?>' onclick="window.parent.postMessage('<?php echo $id; ?> click_delete', 'http://'+location.host+'/HomeDashboard/dashboard.php')">
  <!-- ok -->
  <img src="/HomeDashboard/images/btn_save.svg" class="grid-item button_ok" id='<?php echo $id; ?>' onclick="window.parent.postMessage('<?php echo $id; ?> click_ok', 'http://'+location.host+'/HomeDashboard/dashboard.php')">
</div>
</body>
</html>