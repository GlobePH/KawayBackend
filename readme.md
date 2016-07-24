# Kaway Backend
This repository holds the backend for the "Kaway" platform. It does not include the neural network yet, as that couldn't be tested without, at minimum, 1 month worth of hourly data from users. For the android application, see [KawayApp](https://github.com/GlobePH/KawayApp).

# Deployment
To deploy this backend, I've included a SQL script. Simply run baseline.sql on any MySQL database, then place this entire repository on a server running PHP with the stack of your choice. Finally, edit the .env file to point to that MySQL database and you're mostly ready to go. Before going into production, make sure you run `php artisan key:generate` to randomize the app key and keep your sessions secure.

# Demo
### A live demo of the kaway backend can be found on [my server](http://www.jcgurango.com/kaway). A designer is also available [here](http://www.jcgurango.com/kaway/designer), for modifying the routes, pending further modifications.