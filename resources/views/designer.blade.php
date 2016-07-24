<html>
	<head>
		<title>Designer</title>
		<link rel="stylesheet" href="{{url('css/bootstrap.min.css')}}" />
		<link rel="stylesheet" href="{{url('css/alertify.core.css')}}" />
		<link rel="stylesheet" href="{{url('css/alertify.default.css')}}" />
		<script type="text/javascript" src="{{url('js/jquery.js')}}"></script>
		<script type="text/javascript" src="{{url('js/bootstrap.min.js')}}"></script>
		<script type="text/javascript" src="{{url('js/ractive.js')}}"></script>
		<script type="text/javascript" src="{{url('js/alertify.min.js')}}"></script>
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?key=AIzaSyB6p0qTSGiEt0rt4NUC9YDc4ijf0el5ICE&libraries=places"></script>
		<style>
			.item {
				box-sizing: border-box;
				padding: 5px;
				border-bottom: 1px solid #E0E0E0;
				display: block;
			}
		</style>
		<script type="text/ractive" id="routeDataTemplate">
			@include('designer_ractive');
		</script>
		<script>
			document.onmousedown = disableclick;
			
			function disableclick(event)
			{
				if(event.button == 2)
				{
					return false;
				}
			}
			
			var map;
			var ractive;
			var searchBox;
			
			var locationMarker = null;
			var locationInfo = null;
			var lastRouteOverlay = null;
			var overlayStack = [];
			var stops = [];
			var lastStopId = -1;
			
			var GMAPS_API_KEY = "AIzaSyB6p0qTSGiEt0rt4NUC9YDc4ijf0el5ICE";
			
			function initialize() {
				var mapOptions = {
				  zoom: 19,
				  center: {lat: 14.55347, lng: 121.05}
				};
				
				map = new google.maps.Map(document.getElementById('map_canvas'),
					mapOptions);
				
				google.maps.event.addListener(map, 'click', function(e) {
					var lat = e.latLng.lat();
					var lng = e.latLng.lng();
					
					if (ractive.get("mode") == "route") {
						ractive.push("waypoints", {
							lat: lat,
							lng: lng
						});
					}
					else if (ractive.get("mode") == "stop") {
						var s;
						
						ractive.push("stops", s = {
							id: lastStopId--,
							lat: lat,
							lng: lng
						});
						
						ractive.push("routeStops", s.id);
					} else {
						alertify.error("Map actions are only available in \"Route\" and \"Stop\" modes.");
					}
				});
				
				$(document).ready(function() {
					ractive = new Ractive({
						el: "#routeData",
						template: "#routeDataTemplate",
						data: {
							mode: "search",
							modes: [
								{
									name: "search",
									displayName: "Search"
								},
								{
									name: "route",
									displayName: "Route"
								},
								{
									name: "stop",
									displayName: "Stops"
								},
								{
									name: "existing",
									displayName: "Existing Routes"
								}
							],
							searchText: "",
							routeName: {!!json_encode($routeName)!!},
							waypoints: {!!json_encode($waypoints)!!},
							routeId: {{$routeId ? $routeId : "null"}},
							stops: {!!json_encode($stops)!!},
							routeStops: {!!json_encode($routeStops)!!},
							foundRoutes: [],
							stopDeleteList: [],
							updatedStops: [],
							baseUrl: "{{url('/designer')}}",
							findStop: function(id) {
								for (var i = 0; i < ractive.get("stops").length; i++) {
									if (ractive.get("stops." + i + ".id") == id) {
										return ractive.get("stops." + i);
									}
								}
								
								return null;
							},
							saving: false
						},
						changeMode: function(mode) {
							this.set("mode", mode);
						},
						deleteWaypoint: function(index) {
							this.splice("waypoints", index, 1);
						},
						moveStopUp: function(i) {
							this.splice("routeStops", i, 1).then(function(item) {
								ractive.splice("routeStops", i - 1, 0, item);
							});
						},
						moveStopDown: function(i) {
							this.splice("routeStops", i, 1).then(function(item) {
								ractive.splice("routeStops", i + 1, 0, item);
							});
						},
						goToStop: function(stop) {
							map.setCenter(new google.maps.LatLng(stop.lat, stop.lng));
							stop.marker.setAnimation(google.maps.Animation.BOUNCE);
							clearInterval(stop.lastTimeoutId);
							
							stop.lastTimeoutId = setTimeout(function() {
								stop.marker.setAnimation(null);
							}, 750);
						},
						save: function() {
							alertify.log("Saving...");
							this.set("saving", true);
							var newStops = [];
							var updatedStops = [];
							
							this.get("stops").forEach(function(stop) {
								if (stop.id < 0) {
									newStops.push({
										id: stop.id,
										lat: parseFloat(stop.lat),
										lng: parseFloat(stop.lng)
									});
								}
							});
							
							this.get("updatedStops").forEach(function(stop) {
								updatedStops.push({
									id: stop.id,
									lat: parseFloat(stop.lat),
									lng: parseFloat(stop.lng)
								});
							});
							
							var saveData = {
								id: this.get("routeId"),
								routeStops: this.get("routeStops"),
								deleteStops: this.get("stopDeleteList"),
								newStops: newStops,
								routeName: this.get("routeName"),
								waypoints: this.get("waypoints"),
								updatedStops: updatedStops
							};
							
							$.post("{{url('/designer/save')}}", saveData, function(id) {
								alertify.success("Saved!");
								
								if (!ractive.get("routeId")) {
									window.location.href = "{{url('/designer/edit')}}" + "/" + id;
								} else {
									window.location.reload();
								}
							})
							.fail(function(response) {
								alertify.error(response.responseText);
							})
							.always(function () {
								ractive.set("saving", false);
							});
						},
						searchRoutes: function(query) {
							alertify.log("Searching...");
							
							$.get("{{url('/designer/search')}}", {
								q: query
							}, function(data) {
								if (JSON.stringify(data) == "{}") {
									data = [];
								}
								
								ractive.set("foundRoutes", data);
								alertify.success("Found " + data.length + " results.");
							}).fail(function(response) {
								alertify.error("Error in search: " + response.responseText);
							});
						}
					});
					
					ractive.observe('stops.*', stopsChanged);
					ractive.observe('routeStops.*', stopsChanged);
					ractive.observe('stopDeleteList.*', stopsChanged);
					
					function stopsChanged(newValue, oldValue, keypath) {
						while (stops.length > 0) { stops.pop().setMap(null); };
						
						ractive.get("stops").forEach(function(stop, sin) {
							if (ractive.get("stopDeleteList").indexOf(stop.id) > -1) {
								return;
							}
							
							var marker = new google.maps.Marker({
								map: map,
								position: stop
							});
							
							if (stop.id > 0 && ractive.get("routeStops").indexOf(stop.id) == -1) {
								marker.setOpacity(0.5);
								
								google.maps.event.addListener(marker, 'click', function(e) {
									ractive.push("routeStops", stop.id);
								});
								
								google.maps.event.addListener(marker, 'rightclick', function(e) {
									var prompt = "Are you sure you want to delete this stop? This action won't be performed until the route is saved. This stop may also be associated with other routes.";
									
									alertify.confirm(prompt, function(e) {
										if (e) {
											ractive.push("stopDeleteList", stop.id);
											
											if (ractive.get("routeStops").indexOf(stop.id) > -1)
												ractive.splice("routeStops", ractive.get("routeStops").indexOf(stop.id), 1);
										}
									});
								});
							} else {
								google.maps.event.addListener(marker, 'click', function(e) {
									ractive.splice("routeStops", ractive.get("routeStops").indexOf(stop.id), 1);
									
									if (stop.id < 0) {
										ractive.splice("stops", sin, 1);
									}
								});
								
								google.maps.event.addListener(marker, 'dblclick', function(e) {
									var prompt = "Are you sure you want to delete this stop?  This action won't be performed until the route is saved. This stop may also be associated with other routes.";
									
									alertify.confirm(prompt, function(e) {
										if (e) {
											ractive.push("stopDeleteList", stop.id);
										}
									});
								});
								
								marker.setDraggable(true);
								
								google.maps.event.addListener(marker, "dragend", function(e) {
									var lat = e.latLng.lat();
									var lng = e.latLng.lng();
									
									ractive.set("stops." + sin + ".lat", lat);
									ractive.set("stops." + sin + ".lng", lng);
									
									var stop = ractive.get("stops." + sin);
									
									if (ractive.get("updatedStops").indexOf(stop) == -1) {
										ractive.push("updatedStops", stop);
									}
								});
							}
							
							stop.marker = marker;
							stops.push(marker);
						});
						
						lastRouteOverlay && lastRouteOverlay.setMap(null);
						var path = [];
						
						ractive.get("routeStops").forEach(function(stopId) {
							ractive.get("stops").forEach(function(stop) {
								if (stop.id == stopId) {
									path.push({
										lat: stop.lat,
										lng: stop.lng
									});
								}
							});
						});
						
						lastRouteOverlay = new google.maps.Polyline({
							path: path,
							geodesic: true,
							strokeColor: '#FFAA00',
							strokeOpacity: 0.45,
							strokeWeight: 3
						});
						
						lastRouteOverlay.setMap(map);
					}
					
					ractive.observe('mode', function(newValue, oldValue, keypath) {
						if (newValue == "existing") {
							
						} else {
							
						}
					});
					
					ractive.observe('waypoints.*', function(newValue, oldValue, keypath) {
						while (overlayStack.length > 0) { overlayStack.pop().setMap(null); };
						
						if (ractive.get("waypoints").length > 0) {
							var coordinatesText = "";
							
							ractive.get("waypoints").forEach(function(waypoint) {
								coordinatesText && (coordinatesText += "|");
								coordinatesText += waypoint.lat + "," + waypoint.lng;
							});
							$.get("https://roads.googleapis.com/v1/snapToRoads?interpolate=true&key=" + GMAPS_API_KEY + "&path=" + coordinatesText, function (data) {
								var path = [];
								path.push(ractive.get("waypoints")[0]);
								
								data.snappedPoints.forEach(function(pt) {
									path.push({
										lat: pt.location.latitude,
										lng: pt.location.longitude
									});
								});
								
								path.push(ractive.get("waypoints")[ractive.get("waypoints.length") - 1]);
								
								var overlay = new google.maps.Polyline({
									path: path,
									geodesic: true,
									strokeColor: '#00AAFF',
									strokeOpacity: 0.75,
									strokeWeight: 5
								});
								
								overlay.setMap(map);
								overlayStack.push(overlay);
							});
						}
					});
				});
			}

			google.maps.event.addDomListener(window, 'load', initialize);
			
			Ractive.transitions.createMapsSearchBox = function (node, complete) {
				searchBox = new google.maps.places.SearchBox(node.element.node);
				
				searchBox.addListener("places_changed", function() {
					var places = searchBox.getPlaces();
					
					if (places.length == 0) {
						alertify.error("No place found with that name.");
					} else {
						var place = places[0];
						var lat = place.geometry.location.lat();
						var lng = place.geometry.location.lng();
						alertify.success("Going to \"" + place.formatted_address + "\"...");
						map.setCenter(new google.maps.LatLng(lat, lng));
					}
				});
			};
		</script>
	</head>
	<body>
		<div style="width: 33%; height: 100%; float: left; border-right: 1px solid #CCCCCC; max-height: 100%; overflow: auto;">
			<div class="container-fluid">
				<h3>Kaway Route Designer</h3>
			</div>
			<div id="routeData">
			</div>
		</div>
		<div id="map_canvas" style="width: 67%; height: 100%; float: left;" oncontextmenu="return false">
		</div>
	</body>
</html>