<!DOCTYPE HTML><html>
<head>
<!-- nice cc0 image site www.svgrepo.com-->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<link rel="icon" type="image/png" href="images/Holzbackofen_32x32.png" sizes="32x32">
	<link rel="icon" type="image/png" href="images/Holzbackofen_96x96.png" sizes="96x96">
	<link rel="apple-touch-icon" sizes="180x180" href="images/Holzbackofen_180x180.png">
    <link rel="stylesheet" type="text/css" href="style/dashboard.css">
    <link rel="stylesheet" type="text/css" href="style/settings.css">
    <script src="javascript/config.js"></script>
    <title>HomeDashboard</title>
</head>

<body id="body" onscroll="body_onscroll()">
    <div id="preset" hidden><?php echo $_GET["preset"] ?? 1; ?></div>
    <!-- iframe content -->
	<div class="container" id="container"></div>
    <!-- Slide-over / Settings -->
	<div class="settings-menu open" id="settings-menu">
        <div class="button-group">
            <input type="button" class="button material-element material-button material-left" id="button_back" onclick="click_sidebar('button');" value=" Back ">
            <input type="button" class="button material-element material-button material-center" id="add_button" onclick="DASHBOARD.grid.add_element()" value=" Add ">
            <input type="button" class="button material-element material-button material-right" id="move_button" onclick="mode='move';DASHBOARD.grid.update();click_sidebar('move');" value=" Move ">
        </div>
        <div class="button-group grid-two">
            <select class="select material-element" id="select_type" onchange="DASHBOARD.widgets.change_html_sizes();">
                <option value='null'>- choose type -</option>
            </select>
            <select class="select material-element" id="select_size">
                <option value='null'>- choose size -</option>
            </select>
        </div>
        <div class="button-group">
            <!--load-->
            <input type="button" style="left:0;" class="button material-element material-button material-left" value=" Load " onclick="DASHBOARD.action('','load');">
            <!--save-->
            <input type="button" style="left:0;" class="button material-element material-button material-center" value=" Save " onclick="DASHBOARD.action('','save');">
            <!--delete-->
            <input type="button" style="left:0;" class="button material-element material-button material-right" value=" Delete " onclick="DASHBOARD.action('','delete');">
        </div>
        <div class="button-group grid-one">
            <select class="select material-element" id="select_preset">
                <option value='null' id="null">- select preset -</option>
            </select>
        </div>
    </div>
</body>
<noscript>Please activate JavaScript!</noscript>

<script src="javascript/dashboard/grid.js"></script>
<script src="javascript/dashboard/widget.js"></script>
<script src="javascript/dashboard/dashboard.js"></script>
<script src="javascript/dashboard/script.js"></script>

</html>
