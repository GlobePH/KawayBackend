<?php
namespace App\Http\Controllers;
use App\Models\ApiKey;
use App\Models\Stop;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Kaway;
use App\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use DB;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class ApiController extends Controller
{
	public function authenticate() {
		if (!Auth::validate(array('email' => Input::get('email'), 'password' => Input::get('password')))) {
			return new JsonResponse([
				'success' => false,
				'error_code' => 'invalid_login',
				'message' => 'Invalid username or password.'
			]);
		}
		
		$key = $this->newKey(User::where("email", Input::get("email"))->first()->id);
		
		return new JsonResponse([
			'success' => true,
			'key' => $key->key
		]);
	}
	
	public function newKey($userId) {
		$key = new ApiKey();
		$key->key = str_random(32);
		$key->user_id = $userId;
		$key->expires_at = date('c', time() + (60 * 60));
		$key->save();
		return $key;
	}
	
	public function test($apiKey) {
		return new JsonResponse([
			'success' => true,
			'key' => $apiKey
		]);
	}
	
	public function globeAuth() {
		$accessToken = Input::get("access_token");
		
		if ($accessToken) {
			$subscriberNumber = Input::get("subscriber_number");
			$user = User::where("email", md5($subscriberNumber))->first();
			
			if (!$user) {
				$user = new User();
			}
			
			$user->name = md5($subscriberNumber);
			$user->email = md5($subscriberNumber);
			$user->remember_token = $accessToken;
			$user->save();
			return new Response("Created");
		} else {
			User::where("email", md5(Input::get("unsubscribed")["subscriber_number"]))->delete();
			return new Response("Deleted");
		}
	}
	
	public function globeKaway(Request $request) {
		$fourdigitshortcode = "9331";
		$smsMessageList = Input::get("inboundSMSMessageList");
		
		foreach ($smsMessageList["inboundSMSMessage"] as $smsMessage) {
			$timestamp = $smsMessage["dateTime"];
			$message = $smsMessage["message"];
			$senderAddress = $smsMessage["senderAddress"];
			$senderAddressTrimmed = substr($senderAddress, 7);
			$break = explode(" ",$message);
			
			$user = User::where("email", md5($senderAddressTrimmed))->first();
			
			if (!$user) {
				return new JsonResponse([
					"success" => false,
					"error_code" => "no_user",
					"message" => "No user found with that subscriber number."
				]);
			}
			
			$response = "";
			
			if (strtolower($break[0]) == "kaway") {
				$stopCode = strtolower($break[1]);
				$key = $this->newKey($user->id)->key;
				$kawayResponse = json_decode($this->kaway($key, $stopCode)->getContent());
				
				if ($kawayResponse->success) {
					$response = "Kaway'd!";
				} else {
					$response = $kawayResponse->message;
				}
			}
			
			if ($response) {
				$payload = json_encode([
					"outboundSMSMessageRequest" => [
						"senderAddress" => "tel:".$fourdigitshortcode,
						"outboundSMSTextMessage" => [
							"message" => $response
						],
						"address" => [
							$senderAddress
						]
					]
				]);
				
				$url = "https://devapi.globelabs.com.ph/smsmessaging/v1/outbound/".$fourdigitshortcode."/requests?access_token=".$user->remember_token;
				
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($payload))
				);
				$result = curl_exec($ch); 
			}
		}
		
		return new Response("OK");
	}
	
	public function kaway($apiKey, $stopCode) {
		$routeStop = RouteStop::where("stop_code", $stopCode)->first();
		
		if (!$routeStop) {
			return new JsonResponse([
				"success" => false,
				"error_code" => "not_found",
				"message" => "Stop code not found."
			]);
		}
		
		$key = ApiKey::where("key", $apiKey)->first();
	
		$kaway = new Kaway();
		$kaway->stop_code = $stopCode;
		$kaway->user_id = $key->user_id;
		$kaway->save();
		
		return new JsonResponse([
			'success' => true
		]);
	}
}