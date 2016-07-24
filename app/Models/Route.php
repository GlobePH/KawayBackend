<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
	public $timestamps = false;
	
	public function stops() {
		return $this->belongsToMany("App\\Models\\Stop")->withPivot("stop_code as stop_code");
	}
	
	public function routeStops() {
		return $this->hasMany("App\\Models\\RouteStop");
	}
	
	public function waypoints() {
		return $this->hasMany("App\\Models\\Waypoint");
	}
}