# HomeDashboard
A widget system for local web pages.

Thanks to Michael Oesterreich for letting me contribute to his project.
He started the project and implemented the MySQL, the first php json handler, the recipe, bakingplan and odk_wfo.php.
The Dashboard was his idea and he figured out how to use the CSS grid. The sensor and microcontroller program was coded by him as well.

**See more in** [documentation.md]("./documentation.md")

### Required

admin/root rights on the server, docker and docker-compose

## Functionality

### Browser support
* developed for safari
* developed using Chrome
* not working well with Firefox

### Widget System

The currently available widgets are conifgured in `/conifg.json`.
You can add your own and the dashboard can load it as an IFrame.
  Requirered tags are: name, display_name, default{ filename }
  if your widget only supports some size you can declare it by default{sizes:"11,12,13,14"}
  if you want to deliver an alternative for the other sizes specify special{filename, sizes}

## Widgets
 - Temperature (for each sensor)<br>
 - timer<br>
 - timer sqare rings<br>
 - recipes<br>
 - website<br>
 - dummy (invinsable)<br>
 - notes (only at last position)<br>

moving, adding, deleting, presets

## Vertical
![image](https://user-images.githubusercontent.com/62812264/138172914-ef2896a4-44cf-441c-bd26-0c5939b8ad9a.png)
## Horizontal
![image](https://user-images.githubusercontent.com/62812264/138173038-d22c4c96-8338-4e78-96ef-d2f37f47a994.png)


# Customization

**By clicking the Settings Widget you open a Menu to Move, Delete, Add Widgets and Load and Save Conifgurations.**

![image](https://user-images.githubusercontent.com/62812264/138173812-38b83561-844a-43d8-a3b6-ee2fe53d6283.png)
![image](https://user-images.githubusercontent.com/62812264/138173868-6ff64967-0af3-4f32-b945-f5481d3a4506.png)

**You can select your Widget Type and your Widget Size and add the item to the grid by pressing the Add button.**

**If you want to change the arrangement you can press the move button and then move the items by clicking the arrows.**

**When you have your wanted Arrangement you save by clicking in the middle.**

![image](https://user-images.githubusercontent.com/62812264/138173149-b7be36d6-517c-4dae-9ca2-8f2eea961e14.png)

**In the Recipe Widget**
You can circle through the recipes by clicking the arrows and export the current one to Mail, PDF and Print, by clikcing the export button on the right.

The edit button opens an older page where you can edit your Recipes.
The bakingplans are in `/odk_bakingplan.php`.


# Sensors

ESP32 temperature sensor.
cpp file `/libs/odk/`




## Software License Agreements

**Inline editor implementation** – https://github.com/ckeditor/ckeditor5-editor-inline <br>
Copyright (c) 2003-2021, [CKSource](http://cksource.com) Frederico Knabben. All rights reserved.

**CKEditor 5 classic editor build** – https://github.com/ckeditor/ckeditor5-build-classic <br>
Copyright (c) 2003-2021, [CKSource](http://cksource.com) Frederico Knabben. All rights reserved.

Licensed under the terms of [GNU General Public License Version 2 or later](http://www.gnu.org/licenses/gpl.html).
**CKEditor** is a trademark of [CKSource](http://cksource.com) Frederico Knabben.
All other brand and product names are trademarks, registered trademarks or service marks of their respective holders.
#
**jsPDF** - https://github.com/MrRio/jsPDF <br>
Copyright (c) 2010-2020, [James Hall](https://github.com/MrRio/)
Copyright (c) 2015-2020, [yWorks GmbH](https://www.yworks.com/)
Licenced under the terms found in `/libs/jspdf/LICENCE`
#
**html2canvas** - https://github.com/niklasvh/html2canvas <br>
Copyright (c) 2012, [Niklas von Hertzen](https://github.com/niklasvh/)
Licenced under the terms found in `/libs/html2canvas/LICENCE`
