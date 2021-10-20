<!DOCTYPE HTML><html>
	<head>
		<link rel="stylesheet" href="style/dashboard.css">
	</head>
<body>
<?php
    $set = 0;
    if (isset($_GET["set"])) {
      $set = $_GET["set"];
    }
  ?>
<div id="php_message" hidden><?php echo $set; ?></div>
<div class="content" id="content-container"></div>
  <div id="multiselect">
  	<input type="button" class="button" onclick="_init_();" value="Init" >
    <div class="selectBox" onclick="showCheckboxes()">
      <select>
        <option >Select Options</option>
      </select>
      <div class="overSelect"></div>
    </div>
    <div id="checkboxes">
      <label for="top">
        <input type="checkbox" name="top" id="select_wfo_top" /><span class="label_text" id="tact_top">Backfach oben</span><label>
      <label for="bottom">
        <input type="checkbox" name="bottom" id="select_wfo_bottom" /><span class="label_text" id="tact_bottom">Backfach unten</span></label>
      <label for="left">
        <input type="checkbox" name="left" id="select_bbq_left" /><span class="label_text" id="tact_left">Grill links</span></label>
      <label for="right">
        <input type="checkbox" name="right" id="select_bbq_right" /><span class="label_text" id="tact_right">Grill rechts</span></label>
      <label for="meat">
        <input type="checkbox" name="meat" id="select_meat" /><span class="label_text" id="tact_meat">Fleisch</span></label>
    </div>
  </div>
</body>
<script src="javascript/odk_temperature_widget.js"></script>
</html>