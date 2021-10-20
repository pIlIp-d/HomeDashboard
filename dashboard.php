<!DOCTYPE HTML><html>
<head>
<!-- nice cc0 image site www.svgrepo.com-->

	<!-- TODO -- Externe Links austauschen um mögliches fehlen der Websites zu verhindern -->
	<!-- jquery in odk_recipies.php ,  odk_temp_widget odk_timer_wfo , test.php , tester.php -->


<!-- todo aufräumen in odk.js-->
	<meta charset="utf-8">
	<script src="http://code.jquery.com/jquery-git.js"></script><!-- TODO remove -->
	
	<!--LICENS in LICENCE.md of CKEditor-editor-inline-->
	<script src="libs/inline-editor.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="apple-mobile-web-app-capable" content="yes">	
	<link rel="icon" type="image/png" href="images/Holzbackofen_32x32.png" sizes="32x32">
	<link rel="icon" type="image/png" href="images/Holzbackofen_96x96.png" sizes="96x96">
	<link rel="apple-touch-icon" sizes="180x180" href="images/Holzbackofen_180x180.png">
	<link rel="stylesheet" type="text/css" href="style/dashboard.css">

</head>
<body class="unselectable" id="body" onscroll="body_onscroll()">

<!-----------------------------------
Slide-over / Settings
--------------------------------------->
	<div class="settings-menu unselectable" id="settings-menu">
		<span class="settings-menu-content" id="head-container">
			
			<input type="button" class="button" id="button_back" onclick="click_sidebar('button');" value=" Back ">
			<input type="button" class="button" id="add_button" onclick="select_add_element()" value=" Add ">
			<input type="button" class="button" id="move_button" onclick="mode='move';grid_update();click_sidebar('button');" value=" Move ">
			<br>
			<div class="settings-menu-select">
				<select class="select settings-menu-content" id="select_type">
					<option value='null'>- choose type -</option>
				</select>
				<select class="select settings-menu-content" id="select_size">
					<option value='null'>- choose size -</option>
				</select>	
				<br>
				<!--load-->
				<input type="button" style="left:0;" class="button" value=" Load " onclick="preset_action('','load');">
				<!--save-->
				<input type="button" style="left:0;" class="button" value=" Save " onclick="preset_action('','save');">
				<!--delete-->
				<input type="button" style="left:0;" class="button" value=" Delete " onclick="preset_action('','delete');">
				<br>
				<select class="select" id="select_preset" style="margin-top: 1.5rem">
					<option value='null' id="null">- select preset -</option>
				</select>
				
			</div>	
		</span>
	</div>
<!--------------------------------------
iframe content
----------------------------------------->
	<div class="container unselectable" id="container"></div>
</body>
<noscript>Please activate JavaScript!</noscript>

<script src="javascript/dashboard.js"></script>
</html>

<!-- TODO only full http requests when server says something has changed -->

<!--TODO config datei-->