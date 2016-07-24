<?php
namespace App\Http\Controllers;
use App\Models\Stop;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Waypoint;
use Illuminate\Support\Facades\Input;
use Symfony\Component\HttpFoundation\JsonResponse;

class DesignerController extends Controller
{
	protected function getDesignerVariables($routeId) {
		$stops = [];
		
		foreach (Stop::all() as $stop) {
			$stops[] = array(
				"id" => $stop->id,
				"lat" => floatval($stop->latitude),
				"lng" => floatval($stop->longitude)
			);
		}
		
		$waypoints = [];
		$routeStops = [];
		$routeName = "";
		
		if ($routeId) {
			$route = Route::find($routeId);
			$routeName = $route->name;
			
			foreach ($route->waypoints as $waypoint) {
				$waypoints[] = array(
					"lat" => floatval($waypoint->latitude),
					"lng" => floatval($waypoint->longitude)
				);
			}
			
			foreach ($route->stops as $stop) {
				$routeStops[] = $stop->id;
			}
		}
		
		$allRoutes = [];
		
		foreach (Route::all() as $route) {
			$allRoutes[] = array(
				"id" => $route->id,
				"name" => $route->name
			);
		}
		
		return array(
			"routeId" => $routeId,
			"routeName" => $routeName,
			"stops" => $stops,
			"waypoints" => $waypoints,
			"routeStops" => $routeStops,
			"allRoutes" => $allRoutes
		);
	}
	
	public function search() {
		$query = Input::get("q");
		return new JsonResponse(Route::where("name", "LIKE", "%" . $query . "%")->get());
	}
	
	public function create() {
		return view("designer", $this->getDesignerVariables(null));
	}
	
	public function edit($routeId) {
		return view("designer", $this->getDesignerVariables($routeId));
	}
	
	public function save() {
		$routeId = Input::get("id");
		
		$routeName = Input::get("routeName");
		if (!$routeName) $routeName = "Unnamed Route";
		
		$waypoints = Input::get("waypoints");
		if (!$waypoints) $waypoints = [];
		
		$routeStops = Input::get("routeStops");
		if (!$routeStops) $routeStops = [];
		
		$newStops = Input::get("newStops");
		if (!$newStops) $newStops = [];
		
		$updatedStops = Input::get("updatedStops");
		if (!$updatedStops) $updatedStops = [];
		
		$deleteStops = Input::get("deleteStops");
		if (!$deleteStops) $deleteStops = [];
		
		$route = new Route();
		
		if ($routeId) {
			$route = Route::find($routeId);
		}
		
		$route->name = $routeName;
		$route->save();
		
		$startWaypoint = null;
		$endWaypoint = null;
		Waypoint::where("route_id", $route->id)->delete();
		
		foreach ($waypoints as $i => $waypoint) {
			$newWaypoint = new Waypoint();
			$newWaypoint->route_id = $route->id;
			$newWaypoint->index = $i;
			$newWaypoint->latitude = $waypoint['lat'];
			$newWaypoint->longitude = $waypoint['lng'];
			$newWaypoint->save();
			
			!$startWaypoint && ($startWaypoint = $newWaypoint->id);
			$endWaypoint = $newWaypoint->id;
		}
		
		$newStopIdMap = [];
		
		foreach ($newStops as $newStop) {
			$createdStop = new Stop();
			$createdStop->latitude = $newStop['lat'];
			$createdStop->longitude = $newStop['lng'];
			$createdStop->save();
			
			$newStopIdMap[$newStop['id']] = $createdStop->id;
		}
		
		foreach ($updatedStops as $updatedStop) {
			if ($updatedStop['id'] > 0) {
				$stop = Stop::find($updatedStop['id']);
				$stop->latitude = $updatedStop['lat'];
				$stop->longitude = $updatedStop['lng'];
				$stop->save();
			}
		}
		
		$route->stops()->detach();
		
		foreach ($routeStops as $i => $routeStop) {
			$id = isset($newStopIdMap[$routeStop]) ? $newStopIdMap[$routeStop] : $routeStop;
			
			$createdRouteStop = new RouteStop();
			$createdRouteStop->index = $i;
			$createdRouteStop->route_id = $route->id;
			$createdRouteStop->stop_id = $id;
			$createdRouteStop->stop_code = substr(md5($createdRouteStop->index . "-" . $createdRouteStop->route_id . "-" . $createdRouteStop->stop_id), 0, 5);
			$createdRouteStop->save();
		}
		
		foreach ($deleteStops as $deleteStop) {
			Stop::where("id", $deleteStop)->delete();
		}
		
		return $route->id;
	}
}