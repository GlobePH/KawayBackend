<?php
namespace App\Http\Middleware;

use App\Models\ApiKey;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Closure;

class ApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
		$key = ApiKey::where("key", $request->apiKey)->first();
		
		if ($key == null) {
			return new JsonResponse(array(
				"success" => false,
				"error_code" => "key_invalid",
				"message" => "No API key found."
			));
		} else {
			if (time() > strtotime($key->expires_at)) {
				return new JsonResponse(array(
					"success" => false,
					"error_code" => "key_expired",
					"message" => "That API key is expired."
				));
			}
		}
		
        return $next($request);
    }
}
