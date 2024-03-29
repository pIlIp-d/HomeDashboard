<!DOCTYPE HTML>
<html>
  <link rel="stylesheet" href="style/move_with_arrows.css">
  <script src="../javascript/config.js"></script>
<body>
<?php
    $id = 0;
    $name = "";
    $arrows = "0000";
    if (isset($_GET["json"])) {
        $json = json_decode($_GET["json"]);
        if (isset($json->arrows)) {
            $arrows = $json->arrows;
        }
        if (isset($json->id)) {
            $id = $json->id;
        }
        if (isset($json->name)) {
            $name = $json->name;
        }
    }
  ?>
<div id="arrows" hidden><?php echo $arrows; ?></div>
<div id="button_id" hidden><?php echo $id; ?></div>
 <span id="name" style="font-family: Arial;z-index: -100" ><?php echo $id; echo $name; ?></span>
<div class="grid-container">
<!--
  buttons return their ID(Number) + their ID(name) through postMessage to dashboard
-->
  <!-- up -->
  <img src="/images/arrow_up.svg" class="grid-item button_up" aria-hidden="true" id='<?php echo $id; ?>'>
  <!-- down -->
  <img src="/images/arrow_up.svg" class="grid-item button_down" id='<?php echo $id; ?>'>
  <!-- left -->
  <img src="/images/arrow_up.svg" class="grid-item button_left" id='<?php echo $id; ?>'>
  <!-- right -->
  <img src="/images/arrow_up.svg" class="grid-item button_right" id='<?php echo $id; ?>'>
  <!-- delete -->
  <div id="btn_delete">
  <img src="/images/btn_close.svg" class="grid-item button_delete" id='<?php echo $id; ?>' onclick="window.parent.postMessage('<?php echo $id; ?> click_delete', 'http://'+location.host+'/dashboard.php')">
  </div>
  <!-- ok -->
  <img src="/images/btn_save.svg" class="grid-item button_ok" id='<?php echo $id; ?>' onclick="window.parent.postMessage('<?php echo $id; ?> click_ok', 'http://'+location.host+'/dashboard.php')">
</div>
</body>
<script type="text/javascript">
  window.addEventListener('DOMContentLoaded', init());

  function init(){
    remove_X_from_dummies();
    grey_out_arrows();
  }

  function grey_out_arrows(){
    var arrows = document.getElementById("arrows").innerHTML;
    arrows = arrows.split("");
    const buttons = [document.querySelector('.button_up'),
                    document.querySelector('.button_right'),
                    document.querySelector('.button_down'),
                    document.querySelector('.button_left')];
    for (let button in buttons){
      if (arrows[button] == 1)
        buttons[button].classList.add("filter");
      else{
        buttons[button].onclick = function(){click(buttons[button].classList[1]);};
      }
    }
  }
  function remove_X_from_dummies(){
    let id = document.getElementById("button_id").innerHTML;
    if (document.getElementById("name").innerHTML.substring(id.length) == "dummy")
      document.getElementById("btn_delete").innerHTML = "";
  }
  function click(sender){
    console.log("------------------");
      window.parent.postMessage('<?php echo $id; ?> '+sender, 'http://'+location.host+'/dashboard.php');
  }
</script>
</html>
