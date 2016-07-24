<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RouteStop extends Model
{
	public $timestamps = false;
	public $table = "route_stop";
	
	public function stops() {
		return $this->belongsTo("App\\Models\\Stop", "stop_id");
	}
}