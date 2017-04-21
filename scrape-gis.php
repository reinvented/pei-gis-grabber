<?php
/**
  * scrape-gis.php - A PHP script to bulk-download Prince Edward Island free GIS data.
  *
  * The Province of Prince Edward Island provides access to free GIS data via its website
  * from http://www.gov.pe.ca/gis/index.php3?number=77543&lang=E
  *
  * Unfortunately they have an (inane) requirement to enter name, email and occupation
  * for every download, making complete download of all files cumbersome.
  *
  * This script solves that problem by automating the download.
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
  * @version 0.2, April 21, 2017
  * @author Peter Rukavina <peter@rukavina.net>
  * @copyright Copyright &copy; 2017, Reinvented Inc.
  * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
  */

/**
  * This is an array of HTML pages on www.gov.pe.ca that hold links to GIS files. Add or modify the links as required.
  */
$gisPages = array("page1.html" => "http://www.gov.pe.ca/gis/index.php3?number=77868&lang=E",
                  "page2.html" => "http://www.gov.pe.ca/gis/index.php3?number=1012857&lang=E");

/**
  * Where to put the GIS files we download. Include trailing slash.
  */
$downloadDir = "gisdata/";

/**
  * Where is the wget utility?
  */
$wget = "/sw/bin/wget";

/**
  * Get this from http://simplehtmldom.sourceforge.net/
  */
require_once("simple_html_dom.php");

/**
  * Loop through each page worth of HTML and pull out the links that start with
  * "license_agreement.php3", which indicates they're a link to a map file download.
  */
$maplinks = array();
foreach($gisPages as $filename => $url) {
    $dom = file_get_html($url);
    $links = $dom->find('td a[href^=license_agreement.php3]');
    foreach ($links as $l) {
        array_push($maplinks,$l->attr['href']);
    }
}

/**
  * Now use 'wget' to download every SHP file directly.
  */
foreach ($maplinks as $key => $link) {
    parse_str($link,$linkparts);
    if ($linkparts['amp;file_format'] == "SHP") {
        $fileURL = "http://www.gov.pe.ca/photos/original/" . $linkparts['license_agreement_php3?name'] . ".SHP.zip";
        system("$wget -O \"$downloadDir" . $linkparts['license_agreement_php3?name'] . ".SHP.zip\" $fileURL");
    }
}
