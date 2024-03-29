# Documentation

Documentation for Architecture, source code and overall devs who want to develop ontop of this project.

# Installation

1. clone the repo  
2. change credentials in `.env` file

```env
# mysql credentials
MYSQL_ROOT_PASSWORD=root_password
MYSQL_USER=db_user
MYSQL_PASSWORD=db_user_pass

# pushover config
PUSHOVER_USER=your_user_token
PUSHOVER_API_KEY=your_api_token
```
and change the webserver port inside `.env` if you want 
```env
# webserver - nginx port
NGINX_PORT=8080
```

## Start of the container
1. `cd project_directory`
2. start a webserver that can be accessed at localhost:${NGINX_PORT} `docker-compose up` or as daemon with `docker-compose up -d`
3. create default tables, by opening `localhost:${NGINX_PORT}/initializer.php` in a browser
and enter the admin credentials once from `.env`

### Simple starting of docker containers
* `cd project_directory`
* `docker-compose up -d`

### Stop docker containers
* `cd project_directory`
* `sudo docker-compose stop`

### Uninstall docker containers
* `cd project_directory`
* `sudo docker-compose down`

## Note
the changes in mysql/mariadb container only apply on the creation of the container.  
User don't change after restart of the container. -> you need to remove the folder,  
if you want new user credentials or change them manually inside the container.  

## Backing Up

Only the `mysql` folder and the config.json file need to be backed up.  
To reinstall a state just place the mysql folder in the ProjectFolder and start docker-composer.


-----
# Usage

## dashboard.php

GET parameters  

`.../dashboard.php?preset=1`
    
    default = 1 (preset numeration starts at 0 -> empty preset)
    parameter determents which preset is loaded at the loading/beginning.
    If there is no preset found for that number, the empty preset 0 is loaded.

  ```
  Main content is loaded inside Iframes.
  The Settings slide over has buttons which interact directly with javascript and selects which are filled after recieving the right data.
  ```

# Widget Config

## config.json minimal Example

Remove unwanted Sizes or configure them for each widget type seperately.
  ```json
  {
    "widget":  [],
    "sizes":  ["11","12","13","14","24","34","44","54","64","74"],
    "devices": []
  }
  ```

### Add sensor device
Insert into `"widget":[]`
  ```json
  {
    "name": "temperature",
    "device_id":"001",
    "type": "device",
    "filename": "temperature.php"
  }
  ```

And add a device into `"devices":[]`

  ```json
  {
    "device_name": "name",
    "display_name": "display Name",
    "device_id": "001",
    "sensors": []
  }
  ```

Add a sensor into `"device"..."sensors":[]`
  ```json
    {
      "sensor_name": "wfo_top",
      "display_name": "Backofen Oben",
      "type":"temp",
      "unit":"°C",
      "sensor_id": "001-1"
    }
  ```

## Adding Widget

----------------

### File for IFrame
Any web path is possible but at least .php and .html files are working fine.
Inside the File you recieve a GET object callled `json` containing the Widget Attributes.

The `HomeDashboard/dashboard` Folder contains all the iFrame files.

### Widget Attributes

Minimal widget config (See more to [Parameters]("TODO"))
```json
{
  "name": "name",
  "display_name": "Display Name",
  "filename": "path to iFrame relativ from 'HomeDashboard/dashboard/' folder"
}
```
* name - internal name must be unice
* display_name - name that's shown to the user (also for Language)
* 

----
### Make iFrame Scrollable

  &emsp;&emsp;add `"scrollable":"yes"`

----
### Only Allow Certain Sizes

  &emsp;&emsp;add `"sizes":["11","12","13","14","24","34","44","54","64","74"]`  
  &emsp;&emsp;and remove unwanted sizes

----
### Add Type

there are predefined Types with speical behaviour but mainly the Type is for describing the function and has no effect on the bahaviour.

#### Special Types

1. `"url"` - allows external url for iframe content if the site allows to be embedded as an iFrame
2. `"device"` - imports all sensors from device linked onto the same file ([more]("#add-sensor-device"))
3. `"move"` - non configurable type for editing the dashboard presets
4. `"dummy"` - non configurable hidden widgets for filling gaps in dashboard (can be moved)

special behaviour of Dashboard to for example pass specific values to iFrame can be configured inside switch in `grid.js GridObject createHTML()`

---------------------------------------------------------
## Values that are send to every Widget by the Dashboard
  * all values from `config.json` (except filename and sizes array if exists)
    + `"name"`
    + `"display_name"`
    + ...
  * extra
    + `"preset_id"` - id for database queries
    + `"id"` - widget id / position
    + `"size"`
    + `"show"` - parameter for different views can be changed from iFrame ([more]("TODO"))
  * if widget is from a device / sensor
    + `"device_id"`
    + `"unit"`  

## Ready to use Widget Types

----------------------------
### Devices
Works for Temperature Humditiy or similar Sensors
```json
{
  "name": "temperature",
  "device_id":"001",
  "type": "device",
  "filename": "temperature.php"
}
```
[Dependency Database Tables]("#Tables")

----
### Website

```json
{
  "name": "website",
  "display_name": "Website",
  "type":"url",
  "filename": "url-to-iFrame-website"
}
```
----
### Timer
```json
{
  "name": "timer",
  "display_name": "Timer",
  "filename": "timer.php",
  "sizes": ["12","13","14","24","34"]
}
```
[Dependency Database Tables]("#Tables")

----
### Recipes

```json
{
  "name": "recipes",
  "display_name": "Rezepte",
  "filename": "recipes.html",
  "scrolling": "yes"
}
```
[Dependency Database Tables]("#Tables")

----
### Bakingplans

```json
[{
  "name": "bakingplan_editor",
  "display_name": "Backplan Bearbeiter",
  "filename": "bakingplan_editor.php",
  "scrolling": "yes"
},
{
  "name": "bakingplan",
  "display_name": "Backplan",
  "filename": "bakingplan.php",
  "scrolling": "yes"
}]
```
[Dependency Database Tables]("#Tables")

----
## Widgets iFrame

### show Value

standart is 0 and it can be toggled to 1 for a secondary view

changing is only possible for iframes from the same host address  
Example Command in javascript
`window.parent.postMessage('id_of_widget set_show', 'http://'+location.host+'/dashboard.php');`

### Add values to specific widgets

    Edit `javascript/dashboard.js`. GridObject.createHTML  
    * add case to switch(sw) with your special "type":"" attribute
    * source += "&your_parameter_name=your_parameter_value"
    * read the get-value on your iframe site

# Credentials

Sql database credentials and message credentials for PushOver API
```json
{
  "db_cred": {
    "username": "sql",
    "password": "your_password",
    "db_name": "database_name",
    "db_host": "localhost:6000"
  },
  "message_cred": {
    "user": "",
    "api_key": ""
  }
}
```

# Database

`initializer.php` helps creating all necessary databases and tables
* first fill db_cred inside `cred.json`  

## Tables

`presets`
* `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT
* `name` VARCHAR(50) NOT NULL
* `grid_object_v` JSON NOT NULL
* `grid_object_h` JSON NOT NULL

`devices`
* `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT
* `name` VARCHAR(50) NOT NULL
* `temp_act` INT NOT NULL
* `temp_min` INT NOT NULL
* `temp_max` INT NOT NULL
* `timecode` long NOT NULL
* `timestring` VARCHAR(20) NOT NULL

`timers`
* `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT
* `preset_id` INT NOT NULL
* `timer_id` INT NOT NULL
* `time` LONG NOT NULL

`recipes_ingredients`
* `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT
* `ingredients_id` INT NOT NULL
* `recipes_id` INT NOT NULL
* `amount` VARCHAR(20) NOT NULL

`recipes`
* `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT
* `name` VARCHAR(100) NOT NULL
* `preparation` TEXT NOT NULL
* `bakingtime` INT NOT NULL
* `bakingtemperature` INT NOT NULL
* `active` BIT NOT NULL

`ingredients`
* `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT
* `name` VARCHAR(70) NOT NULL

`bakingplans_recipes`
* `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT
* `recipes_id` INT NOT NULL
* `bakingplans_id` INT NOT NULL
* `order_no` INT NOT NULL

`bakingplans`
* `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT
* `name` VARCHAR(50) NOT NULL
* `type` VARCHAR(10) DEFAULT NULL

## Database API

`db_handler.php` file handles all requests and interacts with the database.

Send your GET request to get values from Database.  
Read documentation inside the db_handler.php file.

overall send a parameter called json that has a json string as value

------
*json=*
```json
{
  "request_name":"name_of_request",
  "other_request_specific_parameters": "and_values"
}
```

-----
### Important requests for your widget

#### active recipe
* `get_active_recipe` -> currently active recipe and its values
*


### DB Handler / API

| Categories  | Method | Parameters            | Example |
|:------------|:------:|-----------------------|---------|
| Recipes     |  GET   | rec_name              |         |
|             |        | rec_bakingtime        |         |
|             |        | rec_bakingtemperature |         |
|             |        | rec_preparation       |         |


## Test Execution
I used PhpStorm to execute execute phpunit.phar tests.  
Maybe I work on a version, that you can execute by running a script later.

(phpunit.phar was placed into ./site/tests and run through phpStorm with php container as interpreter)
<!-- TODO a script to execute it automatically-->
