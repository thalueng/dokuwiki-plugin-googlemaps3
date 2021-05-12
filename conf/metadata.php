<?php
/**
 * DokuWiki Plugin Googlemaps3
 *
 * @license		GPL 3 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author		Bernard Condrau <bernard@condrau.com>
 * @version		2021-05-12, for Google Maps v3 API and DokuWiki Hogfather
 * @see			https://www.dokuwiki.org/plugin:googlemaps3
 * @see			https://www.dokuwiki.org/plugin:googlemaps
 * 
 * Complete rewrite of Christopher Smith's Google Maps Plugin from 2008 with additional functionality
 * metadata.php	plugin configuration metadata
 */
$meta['key']    = array('string');
$meta['region'] = array('string');
$meta['language'] = array('string');
$meta['path'] = array('string');
$meta['delim'] = array('string');