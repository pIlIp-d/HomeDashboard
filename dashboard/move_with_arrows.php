<!DOCTYPE HTML>
<html>
<style type="text/css">
  html {
    margin:  0;
  } 
   .grid-container {
      background-color: white;
      height: 100%;
      width: 100%;
    }
    .grid-item {
      background-color: white;
      position: absolute;
      height: auto;
      width: auto;
      font-size: 1.2rem;
      text-align: center;
      font-weight: bold;
      width: 1.7rem;
      height: 1.7rem;
    }

/*-------------------------------------
------  Button Positions  -------------
--------------------------------------*/
    .button_ok {
      left: calc(50% - 0.45em);
      top:  calc(50% - 0.65em);
      width:  1.4rem;
      height:  1.4rem;
    }
    .button_delete {
      right: 5px;
      top:  5px;
    }
    .button_right {
      transform: rotate(90deg);
      right: 5px;
      top:  calc(50% - 0.65em);
    }
    .button_left {
      transform: rotate(-90deg);
      left: 5px;
      top:  calc(50% - 0.65em);
    }
    .button_up{
      left: calc(50% - 0.6em);
      top:  5px;
    }
    .button_down { 
      transform: rotate(180deg);
      left: calc(50% - 0.6em);
      bottom:  5px;
    }
    #name {

      width: 95%;
      opacity: 25%;
      position: absolute;
      align-content: center;
      font-size: 1.5rem;
    }
</style>
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