<!DOCTYPE HTML>
<html>
<style type="text/css">
	html {
		margin:  0;
		display:  inline-block;
		transform-origin: top left;
	}
	
	.grid-container {
        display: grid;
        grid-template-columns: 33% 33% 33%;
        grid-template-rows: 1.6rem 1.6rem 1.6rem;
        background-color: white;
       	height: 100%;
       	width: 100%;
      }
      .grid-item {
        background-color: #888888;
        border: 0.01rem solid rgba(0, 0, 0, 0.8);
       	padding:  0.1rem;
        font-size: 0.8rem;
        text-align: center;
        font-weight: bold;
      }
</style>
<body>
<?php
		$id = 0;
		if (isset($_GET["id"])) {
			$id = $_GET["id"];
		}
	?>
<div id="button_id" hidden><?php echo $id; ?></div>
<div class="grid-container">
  <div class="grid-item"></div>
  <div class="grid-item" id='<?php echo $id; ?>' onclick="window.parent.postMessage('<?php echo $id; ?> click_up', 'http://192.168.115.9:80/HomeDashboard/dashboard.php')">/\</div>
  <div class="grid-item" id='<?php echo $id; ?>' onclick="window.parent.postMessage('<?php echo $id; ?> click_delete', 'http://192.168.115.9:80/HomeDashboard/dashboard.php')" style="font-size: 0.9rem;">❌</div>

  <div class="grid-item" id='<?php echo $id; ?>' onclick="window.parent.postMessage('<?php echo $id; ?> click_left', 'http://192.168.115.9:80/HomeDashboard/dashboard.php')"><</div>
  <div class="grid-item" id='<?php echo $id; ?>' onclick="window.parent.postMessage('<?php echo $id; ?> click_ok', 'http://192.168.115.9:80/HomeDashboard/dashboard.php')">✔</div>
  <div class="grid-item" id='<?php echo $id; ?>' onclick="window.parent.postMessage('<?php echo $id; ?> click_right', 'http://192.168.115.9:80/HomeDashboard/dashboard.php')">></div>  
  <div class="grid-item"></div>
  <div class="grid-item" id='<?php echo $id; ?>' onclick="window.parent.postMessage('<?php echo $id; ?> click_down', 'http://192.168.115.9:80/HomeDashboard/dashboard.php')">\/</div>
  <div class="grid-item"></div>  
</div>
</body>
</html>