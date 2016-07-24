<div class="container-fluid">
	<ul class="nav nav-tabs">
		{{#modes}}
			<li class="{{#if mode == name}}active{{/if}}" on-click="changeMode(this.name)"><a href="#">{{displayName}}</a></li>
		{{/modes}}
	</ul>
	<div class="row" style="margin-top: 10px;">
		{{#if mode == "search"}}
			<div class="container-fluid">
				Use the search box below to find a location by name.<br /><br />
				<input type="text" class="form-control" value="{{searchText}}" intro="createMapsSearchBox" />
			</div>
		{{/if}}
		{{#if mode == "route"}}
			<div class="container-fluid">
				<a href="{{baseUrl}}" class="btn btn-primary">New Route</a><br />
				<h4>Instructions</h4>
				<b>Left-click</b> on the map to add a new waypoint to the route. If you need to remove a waypoint, do so below.<br />
				<br />
				<h4>Name</h4>
				<input type="text" class="form-control" value="{{routeName}}" /><br />
				<br />
				<h4>Waypoints</h4>
				{{#waypoints :w}}
					{{lat}}, {{lng}}
					{{#if w == waypoints.length - 1}}
						<a href="#" on-click="deleteWaypoint(w)"><i class="glyphicon glyphicon-remove"></i></a>
					{{/if}}
					<br />
				{{/waypoints}}
			</div>
		{{/if}}
		{{#if mode == "existing"}}
			<div class="container-fluid">
				<h4>Instructions</h4>
				Search for an existing route by name using the box below.<br />
				<br />
				<input type="text" class="form-control" value="{{routeSearchText}}" />
				<button type="button" on-click="searchRoutes(routeSearchText)" class="btn btn-secondary pull-right" style="margin-top: 10px;">Search</button><br />
				<br />
				{{#foundRoutes}}
					<a class="item" href="{{baseUrl}}/edit/{{id}}">
						{{name}} (ID: {{id}})
					</a>
				{{/foundRoutes}}
			</div>
		{{/if}}
		{{#if mode == "stop"}}
			<div class="container-fluid">
				<h4>Instructions</h4>
				<b>Left-click</b> on the map to add a new stop. Any stops you add will automatically be associated with the current route. To add existing stops to the current route, just right click them, too. You can also move stops by dragging them.<br />
				<b>Left-click</b> on a stop associated with this route to dissociate it with this route.<br />
				<b>Right-click</b> on a stop to delete it.<br />
				<br />
				<h4>Stops in this Route</h4>
				{{#routeStops :si}}
					{{#if findStop(this)}}
						<div class="item">
						<a href="#" on-click="goToStop(findStop(this))">(ID: {{findStop(this).id.toString().replace("-", "N-")}})</a>
						{{findStop(this).lat}},
						{{findStop(this).lng}}
						
						{{#if si > 0}}<a href="#" on-click="moveStopUp(si)"><i class="glyphicon glyphicon-hand-up"></a>{{/if}}
						{{#if si < routeStops.length - 1}}<a href="#" on-click="moveStopDown(si)"><i class="glyphicon glyphicon-hand-down"></a>{{/if}}
						</div>
					{{/if}}
				{{/routeStops}}
				<br />
				<h4>All Other Stops</h4>
				{{#stops :w}}
					{{#if id > 0 && routeStops.indexOf(id) == -1}}
						<div class="item">
							<span {{#if stopDeleteList.indexOf(id) > -1}}style="color: red;" title="This stop will be deleted when you save."{{/if}}>
								<a href="#" on-click="goToStop(this)">(ID: {{this.id.toString().replace("-", "N-")}})</a>
								{{lat}}, {{lng}}
								{{#if stopDeleteList.indexOf(id) > -1}}
									<a href="#" on-click="undoDeleteStop(id)"><i class="glyphicon glyphicon-repeat" style="transform: scaleX(-1);"></a>
								{{/if}}
							</span>
						</div>
					{{/if}}
				{{/stops}}
			</div>
		{{/if}}
	</div>
	<div class="row" style="margin-top: 20px;">
		<div class="container-fluid">
			<h4>Save Changes</h4>
			<button type="button" class="btn btn-primary pull-right" {{#if saving}}disabled{{/if}} on-click="save()">Save</button>
		</div>
	</div>
</div>