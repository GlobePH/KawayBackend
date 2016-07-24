@extends('template')

@section('head')
  <style>
    .hail[disabled] {
      opacity: 0.5;
    }
    
    .hail {
      cursor: pointer;
    }
  </style>
  <script type="text/javascript" src="http://maps.google.com/maps/api/js?key=AIzaSyB6p0qTSGiEt0rt4NUC9YDc4ijf0el5ICE&libraries=places"></script>
  <script>
    var GMAPS_API_KEY = "AIzaSyB6p0qTSGiEt0rt4NUC9YDc4ijf0el5ICE";
    var routeOverlays = {};
    var lastStopMarker = null;
    
    Math.seed = 6;
    
    Math.seededRandom = function(max, min) {
        max = max || 1;
        min = min || 0;

        Math.seed = (Math.seed * 9301 + 49297) % 233280;
        var rnd = Math.seed / 233280;

        return min + rnd * (max - min);
    }
    
    function initialize() {
      alertify.log("Loading...");
      var apiKey = "";
      var paths = [];
      
      $.get("{{url('/api/authenticate')}}", function(data) {
        if (data.success) {
          apiKey = data.key;
          
          var mapOptions = {
            zoom: 19,
            center: {lat: 14.55347, lng: 121.05}
          };

          map = new google.maps.Map(document.getElementById('map_canvas'),
            mapOptions);

          $('<div/>').addClass('centerMarker').appendTo(map.getDiv());
          
          var lastTimeout = null;
          
          $("#routeList").change(function() {
            for (var id in routeOverlays) { 
              routeOverlays[id].strokeOpacity = (0.35);
              routeOverlays[id].setVisible(false);
              routeOverlays[id].setVisible(true);
            }
            
            if ($(this).val()) {
              routeOverlays[$(this).val()].strokeOpacity = (1);
              routeOverlays[$(this).val()].setVisible(false);
              routeOverlays[$(this).val()].setVisible(true);
              var center = map.getCenter();
              $.get("{{url('/api')}}/" + apiKey + "/routes/" + $(this).val() + "/stops/" + center.lat() + "/" + center.lng(), function (data) {
                if (data.success) {
                  var stop = data.stops[0];
                  lastStopMarker && lastStopMarker.setMap(null);
                  
                  lastStopMarker = new google.maps.Marker({
                    map: map,
                    position: {
                      lat: parseFloat(stop.latitude),
                      lng: parseFloat(stop.longitude)
                    }
                  });
                  
                  lastStopMarker.setOpacity(1);
                  lastStopMarker.setMap(map);
                } else {
                  alertify.error(data.message);
                }
              });
            }
          });
          
          google.maps.event.addListener(map, 'bounds_changed', function(e) {
            clearTimeout(lastTimeout);
            
            lastTimeout = setTimeout(function() {
              var center = map.getCenter();
              $.get("{{url('/api/')}}" + "/" + apiKey + "/routes/near/" + center.lat() + "/" + center.lng(), function(data) {
                if (data.success) {
                  $("#routeList").empty();
                  $("#routeList").append($("<option />").attr("value", ""));
                  
                  while (paths.length > 0) {
                    paths.pop().setMap(null);
                  }
                  
                  data.routes.forEach(function(route) {
                    $("#routeList").append($("<option />").attr("value", route.id).text(route.name));
                    var coordinatesText = "";
                    
                    route.waypoints.forEach(function(waypoint) {
                      if (coordinatesText) {
                        coordinatesText += "|";
                      }
                      
                      coordinatesText += waypoint.latitude + "," + waypoint.longitude;
                    });
                    
                    $.get("https://roads.googleapis.com/v1/snapToRoads?interpolate=true&key=" + GMAPS_API_KEY + "&path=" + coordinatesText, function (data) {
                      var path = [];
                      
                      data.snappedPoints.forEach(function(pt) {
                        path.push({
                          lat: pt.location.latitude,
                          lng: pt.location.longitude
                        });
                      });
                      
                      Math.seed = route.id;
                      
                      var overlay = new google.maps.Polyline({
                        path: path,
                        geodesic: true,
                        strokeColor: 
                          "#" +
                          Math.floor(Math.seededRandom(0, 255)).toString(16) +
                          Math.floor(Math.seededRandom(0, 255)).toString(16) +
                          Math.floor(Math.seededRandom(0, 255)).toString(16)
                        ,
                        strokeOpacity: 0.35,
                        strokeWeight: 5
                      });

                      overlay.setMap(map);
                      paths.push(overlay);
                      routeOverlays[route.id] = overlay;
                    });
                  });
                } else {
                  alertify.error(data.message);
                }
              });
            }, 500);
          });
        } else {
          alertify.error(data.message);
        }
      });
    }

    google.maps.event.addDomListener(window, 'load', initialize);
  </script>
  <style>
    #map_canvas .centerMarker{
      position:absolute;
      /*url of the marker*/
      background:url(http://maps.gstatic.com/mapfiles/markers2/marker.png) no-repeat;
      /*center the marker*/
      top:50%;left:50%;
      z-index:1;
      /*fix offset when needed*/
      margin-left:-10px;
      margin-top:-34px;
      /*size of the image*/
      height:34px;
      width:20px;
    }
  </style>
@endsection

@section('body')
  <div id="map_canvas" style="width: 100%; height: 100%">
  </div>
  <div style="position: fixed; top: 0px; left: 0px; width: 50%;" class="container-fluid">
    <div class="row">
      <div class="col-lg-12">
        <select class="form-control" id="routeList">
          
        </select><br />
        <img src="img/kaway_hail.png" class="hail pull-right" disabled />
      </div>
    </div>
  </div>
@endsection