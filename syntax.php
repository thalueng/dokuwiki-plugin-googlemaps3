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
 * syntax.php	plugin syntax definition
 */
// Must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Syntax for Google Maps v3
 */
class syntax_plugin_googlemaps3 extends DokuWiki_Syntax_Plugin {

	private $mapID = 0;
	private $markerID = 0;

	private $defaultMapOptions = array(
		'mapID' => 0,						// used to allow css override
		'type' => 'roadmap',				// roadmap, hybrid, satellite, terrain
		'width' => '',						// default style in css file
		'height' => '',						// default style in css file
		'lat'  => 12.57076,					// lat+lng are mandatory
		'lng' => 99.96260,					// lat+lng are mandatory
		'address' => '',
		'zoom' => 0,						// zoom is mandatory
		'language' => '',					// google maps defaults to language set in browser 
		'region' => '',						// google maps defaults region bias to US
		'disableDefaultUI' => 0,			// google maps UI defaults
		'zoomControl' => 1,
		'mapTypeControl' => 1,
		'scaleControl' => 1,
		'streetViewControl' => 1,
		'rotateControl' => 1,
		'fullscreenControl' => 1,
		'kml' => 'off',
    );
	private $defaultMarkerOptions = array(
		'markerID' => 0,					// used to allow css override
		'lat'  => 0,
		'lng' => 0,
		'title' => '',
		'icon' => '',
		'info' => '',
		'dir' => '',
		'img' => '',
		'width' => '',
	);
	/**
     * Syntax Type
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     *
     * @return string
     */
    public function getType() {
        return 'substition';
    }

    /**
     * Paragraph Handling
     *
     * @return string
     */
    public function getPType() {
        return 'block';
    }

    /**
     * Sort for applying this mode
     *
     * @return int
     */
    public function getSort() {
        return 900;
    }

    /**
     * @param string $mode
     */
    function connectTo($mode) { 
	   $this->Lexer->addSpecialPattern('<googlemaps3 ?[^>\n]*>.*?</googlemaps3>',$mode,'plugin_googlemaps3');
    }

    /**
     * Handler to prepare matched data for the rendering process
     *
     * @param   string       $match   The text matched by the patterns
     * @param   int          $state   The lexer state for the match
     * @param   int          $pos     The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  bool|array Return an array with all data you want to use in render, false don't add an instruction
     */
	function handle($match, $state, $pos, Doku_Handler $handler){

		static $initialised = false;

		list($mapOptions, $markerOptions) = explode('>',substr($match,12,-14),2);

		$map = $this->getMapOptions($mapOptions);
		$markers = $this->getMarkers($markerOptions);

		// determine width and height (inline styles) for the map image
		if ($map['width'] || $map['height']) {
			$style = $map['width'] ? 'width: '.(is_numeric($map['width']) ? $map['width'].'px' : $map['width']).";" : "";
			$style .= $map['height'] ? 'height: '.(is_numeric($map['height']) ? $map['height'].'px' : $map['height']).";" : "";
//			$style = $map['width'] ? 'width: '.$map['width'].";" : "";
//			$style .= $map['height'] ? 'height: '.$map['height'].";" : "";
			$style = "style='$style'";
		} else {
			$style = '';
		}
		unset($map['width'],$map['height']);

		// determine region and language
		$lang  = ($map['region'] ? '&region='.$map['region'] : ($this->getConf('region') ? '&region='.$this->getConf('region') : ''));
		$lang .= ($map['language'] ? '&language='.$map['language'] : ($this->getConf('language') ? '&language='.$this->getConf('language') : ''));
		unset($map['region'],$map['language']);

		// create a javascript parameter string for the map
		$jsOptions = '';
		foreach ($map as $key => $val) {
			$jsOptions .= is_numeric($val) ? "$key : $val," : (is_bool($val) ? "$key : ".(int)$val."," : "$key : '".hsc($val)."',");
		}

		// create a javascript serialisation of the markers data
		$jsMarker = '';
		if (!empty($markers)) {
			foreach ($markers as $marker) {
				$jsMarker .=	"{markerID:".$marker['markerID'].", lat:".$marker['lat'].", lng:".$marker['lng'].
									($marker['title'] ? ", title:'".$marker['title']."'" : "").
									($marker['icon'] ? ", icon:'".$marker['icon']."'" : "").
									($marker['info'] ? ", info:'".$marker['info']."'" : "").
									($marker['dir'] ? ", dir:'".$marker['dir']."'" : "").
									($marker['img'] ? ", img:'".$marker['img']."'" : "").
									($marker['width'] ? ", width:'".$marker['width']."'" : "").
          						"},";
				}
				$jsMarker = "marker : [ ".$jsMarker." ]";
		}

		if ($initialised) {
			$jsData = '';
		} else {
			$initialised = true;
			$jsData = 'var googlemaps3 = new Array();';
		}
		$jsData .= "googlemaps3[googlemaps3.length] = {".$jsOptions.$jsMarker." };";
		return array($map['mapID'], $style, $lang, $jsData);
	}

    /**
     * Renders the map in the wiki page
     *
     * @param string        $mode     output format being rendered
     * @param Doku_Renderer $renderer the current renderer object
     * @param array         $data     data created by handler()
     * @return  boolean                 rendered correctly? (however, returned value is not used at the moment)
     */
	function render($mode, Doku_Renderer $renderer, $data) {

		static $initialised = false;

		if ($mode == 'xhtml') {
			list($mapID, $style, $lang, $jsData) = $data;

			// include script only once
			if (!$initialised) {
				$initialised = true;
				$renderer->doc .= "<script src='https://maps.googleapis.com/maps/api/js?key=".$this->getConf('key').$lang."&callback=initMap' defer></script>";
			}
			$renderer->doc .= "<script>$jsData</script>";
			$renderer->doc .= "<div id='googlemaps3map".$mapID."' class='googlemaps3'".($style ? ' '.$style : '')."></div>";
			return true;
		}
		return false;
	}

    /**
     * extract map options
     *
     * @param	string	$pattern	string of map options
     * @return	array				associative array of map options
     */
	private function getMapOptions($pattern) {

		$options = array();
		preg_match_all('/(\w*)="(.*?)"/us', $pattern, $options, PREG_SET_ORDER);

		// parse match for instructions
		$map = $this->defaultMapOptions;
		$map['mapID'] = ++$this->mapID;
		foreach($options as $option) {
			list($match, $key, $val) = $option;
			if (isset($map[$key])) if ($key=='kml') $map[$key] = $val; else $map[$key] = strtolower($val);
			if (isset($map[$key])) {
				if ($val=='true') $val = 1; elseif ($val=='false') $val = 0;
				$map[$key] = $val;
			}
		}
		return $map;
	}

    /**
     * extract markers information
     *
     * @param	string	$pattern	multi-line string of markers
     * @return	array				multi-dimensional associative array of markers
     */
	private function getMarkers($pattern) {

		$dlm = $this->getConf('delim');
		$markers = array();
		preg_match_all('/.+/', $pattern, $lines, PREG_PATTERN_ORDER); // get all markers
		foreach ($lines[0] AS $line) {
			preg_match_all('/(?<=\\'.$dlm.'|^|\n)(.*?)(?=\\'.$dlm.'|$|\n)/u', $line, $matches,PREG_PATTERN_ORDER); // get marker options
			$markers[] = array_combine(array_keys($this->defaultMarkerOptions), array_merge(array(0), $matches[0], array_fill(0, count($this->defaultMarkerOptions)-count($matches[0])-1,'')));
		}

		// trim leading "delimiter" if any and get default if option empty
		foreach ($markers as $mark => $marker) {
			foreach ($marker as $option => $value) {
				if ($value) $markers[$mark][$option] = ltrim($markers[$mark][$option], $dlm);
				if (!$value) $markers[$mark][$option] = $this->defaultMarkerOptions[$option];
			}
		}
		foreach ($markers as $mark => $marker) {
			$markers[$mark]['markerID'] = ++$this->markerID;
			if ($markers[$mark]['lat'] == 'address') {
				$markers[$mark]['lat'] = "'".$markers[$mark]['lat']."'";
				$markers[$mark]['lng'] = "'".$markers[$mark]['lng']."'";
			} else {
				$markers[$mark]['lat'] = is_numeric($marker['lat']) ? floatval($marker['lat']) : 0;
				$markers[$mark]['lng'] = is_numeric($marker['lng']) ? floatval($marker['lng']) : 0;
			}
			$markers[$mark]['icon'] = ($marker['icon'] && strpos($marker['icon'], '.') ? $this->getConf('path').$marker['icon'] : $marker['icon']);
			$markers[$mark]['info'] = str_replace("\n","", p_render("xhtml", p_get_instructions($markers[$mark]['info']), $info));
		}
		return $markers;
    }
}
