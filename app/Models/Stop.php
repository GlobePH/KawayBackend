<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Stop extends Model
{
	public $timestamps = false;
	
	public function routes() {
		return $this->belongsToMany("App\\Models\\Route", "route_stop");
	}
}