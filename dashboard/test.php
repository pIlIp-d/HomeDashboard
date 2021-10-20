<!DOCTYPE HTML>
<html>
  <head>
    <title></title>
    <meta charset="utf-8"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>

    <style type="text/css">

      #filecontainer {
      width: 100%;
      height: 50%;
      }
    </style>
  </head>
  <body>
    <noscript>Please activate JavaScript!</noscript>
 
  <div id="click_div" hidden onclick="clicker();"></div>
  
    <iframe class="iframe" src="test_.php?id=16" id="filecontainer">

 <script type="text/javascript">
  

window.addEventListener('message', e => {
          console.log(e.data);
          if (e.origin == "http://192.168.115.9"
              && e.data == "CallFunctionA") {
              FunctionA();
          }
      }, false);
      function FunctionA() {
          document.getElementById("click_div").click();
      }

    var test= "iougasfdoiuh";
  function clicker(){
console.log("worked ioubz");
}

  /*
  $('#filecontainer').load(function(){

    var iframe = $('#filecontainer').contents();


    iframe.find("input").click(function(){
      console.log("iframe: "+ iframe);
      var test = $(this);
      console.log("pressed");
      alert("pressed "+this.id);

    });
  });*/
  </script>
  </body>
 
</html>