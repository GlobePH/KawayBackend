# KawayBackend
This repository holds the backend for the Kaway application platform. For the android application, see [KawayApp](https://github.com/GlobePH/KawayApp).

# Demo
A live demo can be found on [my server](http://www.jcgurango.com/kaway/). You can find our in-house designer tool [here](http://www.jcgurango.com/kaway/designer).

# Installation
The `baseline.sql` file is included for inspection of the database schema.

# API Documentation
## Using the Endpoints
To use the API endpoints, you first need to perform a POST to the following endpoint:

`http://www.jcgurango.com/kaway/api/authenticate`

Details of the request can be found below. Once you’ve gotten an API key you can use it like this.

`http://www.jcgurango.com/kaway/api/YOUR_API_KEY_HERE/endpoint`

For example,

`GET http://www.jcgurango.com/kaway/api/YOUR_API_KEY_HERE/routes/near/1.50392/14.51023`

## Error Handling
All endpoints return a "success" flag and a "message" property. If there were any errors, it will return "false" on the success flag and give you a message to display to the user, as well as an "error_code" property so you can create your own error messages. For example:

```js
{
	success: false,
	error_code: "sample_error",
	message: "This is a sample error. Something has gone terribly wrong."
}
```

## Endpoints
### POST /authenticate
Authenticates the user and returns an API key.

#### Input
```js
{
	email: "",
	password: ""
}
```

#### Output
```js
{
	success: true,
	key: "An API key goes here."
}
```

### GET /routes/near/{latitude}/{longitude}
Finds all routes near a given latitude and longitude. “Near” is defined as <100 meters away. The waypoints need to be "snapped to roads" using the Google maps API to look good, with interpolation set to "true".

#### Output
```js
{
	"success" : true,
	"routes" : [{
			"id" : 31,
			"name" : "Lane Q to 31st Street",
			"source_id" : null,
			"destination_id" : null,
			"waypoints" : [{
					"id" : 48,
					"route_id" : 31,
					"index" : 0,
					"latitude" : "14.5534105",
					"longitude" : "121.0491556"
				}, {
					"id" : 49,
					"route_id" : 31,
					"index" : 1,
					"latitude" : "14.5530756",
					"longitude" : "121.0502848"
				}, {
					"id" : 50,
					"route_id" : 31,
					"index" : 2,
					"latitude" : "14.5527511",
					"longitude" : "121.0511485"
				}, {
					"id" : 51,
					"route_id" : 31,
					"index" : 3,
					"latitude" : "14.5521228",
					"longitude" : "121.0519854"
				}
			]
		}
	]
}

```

### GET /routes/{routeid}/stops/{latitude}/{longitude}
Gets all the stops for a given route.

#### Output
```js
{
	"success" : true,
	"stops" : [{
			"distance" : "0.007692",
			"stop_code" : "51832",
			"pivot" : {
				"route_id" : 31,
				"stop_id" : 29
			}
		}
	]
}
```

### GET /kaway/{stopcode}
Kaways.

#### Output
```js
{
	success: true
}
```