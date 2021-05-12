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
		if (googlemaps3[i].address != '') {
			pantoMapAddress(googlemaps3[i].map,googlemaps3[i].address);
		}
		if (googlemaps3[i].marker && googlemaps3[i].marker.length > 0) {
			for (j=0; j<googlemaps3[i].marker.length; j++) if (googlemaps3[i].marker[j].lat=='address') {
				attachAddressMarker(googlemaps3[i].map,googlemaps3[i].marker[j]);
			} else {
				attachMarker(googlemaps3[i].map,googlemaps3[i].marker[j]);
			}
		}
		if (googlemaps3[i].kml != 'off') {
			const georssLayer = new google.maps.KmlLayer({url: googlemaps3[i].kml, map: googlemaps3[i].map,});
		}
	}
	function attachMarker(map, options, position) {
		// location from latlng coordinates
		if (position == null) {
			position = {lat: options.lat, lng: options.lng};
			origin = options.lat+','+options.lng;
		// location from address
		} else {
			origin = options.lng;
		}
		const marker = new google.maps.Marker({
			position: position,
			map: map,
			title: options.title,
			icon: options.icon,
		});
		if (options.img || options.info || options.dir) {
			markerInfo =
				'<div id="googlemaps3marker'+options.markerID+'" class="googlemaps3 markerinfo"'+(options.width ? ' style="width: '+options.width+'"' : '')+'>' +
					(options.title ? '<h3>'+options.title+'</h3>' : '') +
					(options.img ? '<img src="'+options.img+'" style="max-width: 100%">' : '') +
					(options.info ? '<p>'+options.info+'</p>' : '') +
					(options.dir ? '<p><a href="https://www.google.com/maps/dir/?api=1&destination='+origin+'" target="_blank">'+options.dir+'</a></p>' : '') +
				'</div>';
			const infoWindow = new google.maps.InfoWindow({content: markerInfo,});
			marker.addListener('click', () => {
				infoWindow.open(map, marker);
			});
		}
	}
	function attachAddressMarker(map, options) {
		const geocoder = new google.maps.Geocoder();
		geocoder.geocode({'address': options.lng}, function(results, status) {
			if (status=='OK') {
				attachMarker(map, options, results[0].geometry.location);
			} else {
				console.log('Googlemaps3 Plugin: geocode failed, status='+status);
			}
		});
	}
	function pantoMapAddress(map, address) {
		const geocoder = new google.maps.Geocoder();
		geocoder.geocode({'address': address}, function(results, status) {
			if (status=='OK') {
				map.setCenter(results[0].geometry.location);
			} else {
				console.log('Googlemaps3 Plugin: geocode failed, status='+status);
			}
		});
	}
}
