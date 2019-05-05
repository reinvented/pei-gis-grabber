<?php
/**
  * scrape-gis.php - A PHP script to bulk-download Prince Edward Island free GIS data.
  *
  * The Province of Prince Edward Island provides access to free GIS data via its website
  * from http://www.gov.pe.ca/gis/index.php3?number=77543&lang=E
  *
  * This script allows you to easily download *all* of the GIS data available in a
  * form suitable for loading into QGIS.
  *
  * This program is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or (at
  * your option) any later version.
  *
  * This program is distributed in the hope that it will be useful, but
  * WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
  * General Public License for more details.
  *
  * You should have received a copy of the GNU General Public License
  * along with this program; if not, write to the Free Software
  * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
  * USA
  *
  * @version 0.2, May 5, 2019
  * @author Peter Rukavina <peter@rukavina.net>
  * @copyright Copyright &copy; 2019, Reinvented Inc.
  * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
  */

/**
  * Where to put the GIS files we download. Include trailing slash.
  */
$downloadDir = "/Volumes/Working/Code/pei-gis/gisdata/";

/**
  * Where is the wget utility?
  */
$wget = "/usr/local/bin/wget";

/**
  * Where is the unzip utility?
  */
$unzip = "/usr/bin/unzip";

/**
  * This is an array of HTML pages on www.gov.pe.ca that hold links to GIS files. Add or modify the links as required.
  */
$gisPages = array("basemap"         => "http://www.gov.pe.ca/gis/index.php3?number=77581&lang=E",
                  "community"       => "http://www.gov.pe.ca/gis/index.php3?number=77584&lang=E",
                  "emergency"       => "http://www.gov.pe.ca/gis/index.php3?number=77552&lang=E",
                  "index"           => "http://www.gov.pe.ca/gis/index.php3?number=77551&lang=E",
                  "resource"        => "http://www.gov.pe.ca/gis/index.php3?number=77555&lang=E",
                  "civicaddress"    => "http://www.gov.pe.ca/gis/index.php3?number=77553&lang=E",
                  "electoral"       => "http://www.gov.pe.ca/gis/index.php3?number=77554&lang=E",
                  "imagery"         => "http://www.gov.pe.ca/gis/index.php3?number=77582&lang=E",
                  "transportation"  => "http://www.gov.pe.ca/gis/index.php3?number=77583&lang=E");

/**
  * Get this from http://simplehtmldom.sourceforge.net/
  */
require_once("simple_html_dom.php");

/**
  * Loop through each page worth of HTML and pull out the links that start with
  * "license_agreement.php3", which indicates they're a link to a map file download.
  */
$maplinks = array();
foreach($gisPages as $folder => $url) {
  $dom = file_get_html($url);
  $links = $dom->find('td a[href^=license_agreement.php3]');
  foreach ($links as $l) {
      array_push($maplinks,array("folder" => $folder, "link" => $l->attr['href']));
  }
}

/**
  * Start off a PyQGIS Script that we'll add to as we iterate.
  */

$fp = fopen($downloadDir . "importlayers.py", "w");
fwrite($fp, "import os\n");
fwrite($fp, "from qgis.core import (\n");
fwrite($fp, "\tQgsVectorLayer\n");
fwrite($fp, ")\n");
fwrite($fp, "root = QgsProject.instance().layerTreeRoot()\n");

/**
  * Now use 'wget' to download every SHP file to directly.
  */
$created_group = array();
foreach ($maplinks as $key => $maplink) {
  parse_str($maplink['link'], $linkparts);
  if ($linkparts['amp;file_format'] == "SHP") {
    $inputfile = "http://www.gov.pe.ca/photos/original/" . $linkparts['license_agreement_php3?name'] . ".SHP.zip";
    if (!file_exists($downloadDir . $maplink['folder'])) {
      mkdir($downloadDir . $maplink['folder']);
    }
    $outputfile = $downloadDir . $maplink['folder'] . '/' . $linkparts['license_agreement_php3?name'] . ".SHP.zip";
    $outputdirectory = $downloadDir . $maplink['folder'] . '/' . $linkparts['license_agreement_php3?name'] . ".SHP";
    if (!file_exists($outputfile)) {
      system("$wget -O \"$outputfile\" $inputfile");
      system("$unzip -d $outputdirectory $outputfile");
    }
    foreach (glob("$outputdirectory/*.shp") as $filename) {
      if (!array_key_exists($maplink['folder'], $created_group)) {
        fwrite($fp, "mygroup = root.addGroup(\"" . $maplink['folder'] . "\")\n");
        $created_group[$maplink['folder']] = TRUE;
      }
      else {
        fwrite($fp, "mygroup = root.findGroup(\"" . $maplink['folder'] . "\")\n");
      }
      fwrite($fp, "mylayer = QgsVectorLayer(\"" . $filename . "\", \"" . $linkparts['license_agreement_php3?name'] . "\", \"ogr\")\n");
      fwrite($fp, "QgsProject.instance().addMapLayer(mylayer, False)\n");
      fwrite($fp, "mygroup.addLayer(mylayer)\n");
      fwrite($fp, "QgsProject.instance().layerTreeRoot().findLayer(mylayer.id()).setItemVisibilityChecked(False)\n");
    }
  }
}

fclose($fp);
