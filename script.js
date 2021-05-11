/**
 * DokuWiki Plugin Googlemaps3
 *
 * @license		GPL 3 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author		Bernard Condrau <bernard@condrau.com>
 * @version		2021-05-11, for Google Maps v3 API and DokuWiki Hogfather
 * @see			https://www.dokuwiki.org/plugin:googlemaps3
 * @see			https://www.dokuwiki.org/plugin:googlemaps
 * 
 * Complete rewrite of Christopher Smith's Google Maps Plugin from 2008 with additional functionality
 * script.js	javascript map initialisation
 */

// initialise all maps, corresponding markers, and kml
function initMap() {
	var nodes = document.body.getElementsByTagName('div');

	var i=0;
	for (var j=0; j<nodes.length; j++) {
		if (nodes[j].className.match(/\bgooglemaps3\b/)) {
			googlemaps3[i++].node = nodes[j];
		}
	}
	for (i=0; i<googlemaps3.length; i++) {
		googlemaps3[i].map = new google.maps.Map(googlemaps3[i].node, {
			center: {lat: googlemaps3[i].lat, lng: googlemaps3[i].lng},
			zoom: googlemaps3[i].zoom,
			mapTypeId: googlemaps3[i].type,
			disableDefaultUI: googlemaps3[i].disableDefaultUI,
			zoomControl: googlemaps3[i].zoomControl,
			mapTypeControl: googlemaps3[i].mapTypeControl,
			scaleControl: googlemaps3[i].scaleControl,
			streetViewControl: googlemaps3[i].streetViewControl,
			rotateControl: googlemaps3[i].rotateControl,
			fullscreenControl: googlemaps3[i].fullscreenControl,
		});
		if (googlemaps3[i].marker && googlemaps3[i].marker.length > 0) {
			for (j=0; j<googlemaps3[i].marker.length; j++) {
				const marker = new google.maps.Marker({
					position: {lat: googlemaps3[i].marker[j].lat, lng: googlemaps3[i].marker[j].lng},
					map: googlemaps3[i].map,
					title: googlemaps3[i].marker[j].title,
					icon: googlemaps3[i].marker[j].icon,
				});
				if (googlemaps3[i].marker[j].img || googlemaps3[i].marker[j].info || googlemaps3[i].marker[j].dir) {
					markerInfo =
						'<div id="googlemaps3marker'+googlemaps3[i].marker[j].markerID+'" class="googlemaps3 markerinfo"'+(googlemaps3[i].marker[j].width ? ' style="width: '+googlemaps3[i].marker[j].width+'"' : '')+'>' +
							(googlemaps3[i].marker[j].title ? '<h3>'+googlemaps3[i].marker[j].title+'</h3>' : '') +
							(googlemaps3[i].marker[j].img ? '<img src="'+googlemaps3[i].marker[j].img+'" style="max-width: 100%">' : '') +
							(googlemaps3[i].marker[j].info ? '<p>'+googlemaps3[i].marker[j].info+'</p>' : '') +
							(googlemaps3[i].marker[j].dir ? '<p><a href="https://www.google.com/maps/dir/?api=1&destination='+googlemaps3[i].marker[j].lat+','+googlemaps3[i].marker[j].lng+'" target="_blank">'+googlemaps3[i].marker[j].dir+'</a></p>' : '') +
						'</div>';
					attachMarkerInfo(marker, markerInfo);
				}
			}
		}
		if (googlemaps3[i].kml != 'off') {
			const georssLayer = new google.maps.KmlLayer({url: googlemaps3[i].kml, map: googlemaps3[i].map,});
		}
	}
	
	function attachMarkerInfo(marker, markerInfo) {
		const infoWindow = new google.maps.InfoWindow({content: markerInfo,});
		marker.addListener('click', () => {
			infoWindow.open(marker.get("map"), marker);
		});
	}
}
