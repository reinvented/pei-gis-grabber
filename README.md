PEI GIS Grabber
===============

The Province of Prince Edward Island, Canada provides access to [free GIS data via its website](http://www.gov.pe.ca/gis/index.php3?number=77543&lang=E).

Unfortunately they have an (inane) requirement to enter name, email and occupation for every download, making complete download of all files cumbersome.

This script solves that problem by automating the download.

Requirements
------------

* PHP 5.x
* PHP Simple HTML DOM Parser (from http://simplehtmldom.sourceforge.net/)
* wget (from http://www.gnu.org/s/wget/)

Usage
-----

Edit the file scrape-gis.php and check and/or modify the $downloadDir and $wget variables.

You may also need to modify the $gisPages array if the "Free GIS Products" links on the [main GIS catalog page](http://www.gov.pe.ca/gis/index.php3?number=77543&lang=E) change or are expanded.

Once everything is in place just do a:

    php scrape-gis.php

and the all of the SHP files linked to from the various free GIS data pages will be downloaded. You can modify the script if you want to get only MID/MIF or NTX files, or only files of a certain name.

Caution
-------

The implication of using the script to automatically download these files is presumably that you agree to the various [licensing agreements](http://www.gov.pe.ca/gis/index.php3?number=77462&lang=E) in place.

This code is not guaranteed to work properly, or at all, and I have no connection other than residency to the Province of Prince Edward Island.

Credits
-------

[Peter Rukavina, Reinvented Inc.](http://ruk.ca/)