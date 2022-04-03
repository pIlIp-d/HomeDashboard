# Documentation

Documentation for Architecture, source code and overall devs who want to develop ontop of this project.

## **dashboard.php**

GET parameters  
    
    preset defaults to 1(preset numeration starts at 0)
    determents which preset is loaded at the beginning.
    if this preset isn't found the empty preset is loaded.

HTML

    Main content is loaded inside Iframes. (id=container)
    The Settings slide over has buttons which interact directly with javascript and selects which are filled after recieving the right data.

## config.json Example

<details><summary>Config.json</summary>
    ```json
    {
      "widget":  [
          {
            "name": "temperature",
            "display_name": "sensor",
            "default": {
              "type": "sensor",
              "filename": "odk_temperature.php"
            }
          },
          {
            "name": "timer",
            "display_name": "Timer",
            "default": {
              "filename": "timer.php",
              "type": "timer",
              "sizes": "12,13,14,24,34"
            },
            "special": {
              "filename": "timer_quadrat.html",
              "type":"timer",
              "sizes": "11,44"
            }
          },
          {
            "name": "recipes",
            "display_name": "Rezepte",
            "default": {
              "filename": "recipes.html",
              "scrolling": "yes"
            }
          },
          {
            "name": "move",
            "widget": "no",
            "display_name": "",
            "default": {
              "type": "move",
              "filename": "move_with_arrows.php"
            }
          },  
          {
            "name": "dummy",
            "attributes": "yes",
            "display_name": "Dummy 1x1",
            "default": {
              "filename": "",
              "sizes": "11"
            }
          },
          {
            "name": "bakingplan_editor",
            "display_name": "Backplan Bearbeiter",
            "default": {
              "filename": "bakingplan_editor.php",
              "scrolling": "yes"
            }
          },
          {
            "name": "bakingplan",
            "display_name": "Backplan",
            "default": {
              "filename": "bakingplan.php",
              "scrolling": "yes"
            }
          }
      ],
      "sizes":  ["11","12","13","14","24","34","44","54","64","74"],
      "devices": [
      {
          "device_name": "name",
          "display_name": "Garden",
          "device_id":"001",
          "sensors":[
              {
                "sensor_name": "pool_temp",
                "display_name": "Water Temperature",
                "type":"temp",
                "unit":"°C",
                "sensor_id": "001-1"
            },
            {
              "sensor_name": "garden_humidity",
              "display_name": "Humidity Garden",
              "type":"temp",
              "unit":"%",
              "sensor_id": "001-2"
            }
          ]
      }
      ]
    }
    ```
</details>

### scrollable widgets

set `scrollable = "yes"` or `"no"`

### widget types

defaults

Add all sensors from devices

button/toggle_button

URL

## Values that are send to every Widget by dashboard

    * name=name
    * display_name=display_name
    * show=(special_value)["#show-Value"]
    * id=id of widget / position

### show Value

standart is 0 and it can be toggled to 1 for a secondary view

changing is only possible for iframes from the same host address  
Example Command in javascript
`window.parent.postMessage('id_of_widget set_show', 'http://'+location.host+'/HomeDashboard/dashboard.php');`

### Add values to specific widgets

    Edit `javascript/dashboard.js`. GridObject.createHTML  
    * add case to switch(sw) with your special "type":"" attribute
    * source += "&your_parameter_name=your_parameter_value"
    * read the get-value on your iframe site
