<?php
namespace App\Http\Controllers;
use App\Models\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use DB;

class RoutesController extends Controller
{
	public function near($apiKey, $latitude, $longitude) {
		$routes = Route::whereHas("stops", function ($q) use ($latitude, $longitude) {
			$q->where(DB::raw("LLDIST(`latitude`, `longitude`, $latitude, $longitude)"), "<", "5");
		})->with("waypoints")->get();
		
		return new JsonResponse(array(
			"success" => true,
			"routes" => $routes
		));
	}
	
	public function stops($apiKey, $routeId, $latitude, $longitude) {
		$route = Route::find($routeId);
		
		if (!$route) {
			return new JsonResponse(array(
				"success" => false,
				"error_code" => "not_found",
				"message" => "No route found."
			));
		}
		
		$stops = $route
			->stops()
			->where(DB::raw("LLDIST(`latitude`, `longitude`, $latitude, $longitude)"), "<", "5")
			->orderBy(DB::raw("LLDIST(`latitude`, `longitude`, $latitude, $longitude)"), "asc")
			->select(DB::raw("LLDIST(`latitude`, `longitude`, $latitude, $longitude) as `distance`"), 'latitude', 'longitude')
			->get();
		
		return new JsonResponse(array(
			"success" => true,
			"stops" => $stops
		));
	}
	
	public function predict($routeId, $dateTime) {
		// STUB'd for now. The neural network isn't used in the demo, as there isn't enough data.
		// To be accurate, it requires at least 30 days (24 hours * 30) worth of data.
	}
}