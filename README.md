# CIP<sup>3</sup>
> Cisco IP Phone Portal - track and manage your Cisco UC endpoints

CIP<sup>3</sup> is an open project to help Cisco Collaboration engineers track and manage devices within their Cisco UCM clusters.

It uses the PHP Laravel framework and contains a Docker workflow to help you get up and running fast.

The current version offers the following:

- Cisco UCM Server(s) CRUD interface
- Sync Cisco UCM Phones/Devices and RisPort data daily and on-demand
- UCM Sync History
- Sync success/fail notifications via Webex Teams BYOB (Bring Your Own Bot)
- Display device information and real-time data (IP Address, Firmware, etc)
- Delete ITL files from IP Phones
- Push custom background images to IP Phones 
- Manage User accounts

Here's some of the packages included:

[Laravel 5.8](www.laravel.com) - the core application framework

[Laravel Backpack](https://laravel-backpack.readme.io/docs) - for rapid UI prototyping

[laravel-chartjs](https://github.com/fxcosta/laravel-chartjs) - build sweet looking charts in your Laravel controllers

[Vessel](https://vessel.shippingdocker.com/) - a Docker dev environment for Laravel

[Webex Teams Integration](https://developer.webex.com/) - send Ucm sync event notifications to Webex Teams

Vessel is a wrapper around Docker and docker-compose that makes it really easy to get setup with a full stack docker environment with just a couple commands.
The Vessel package and a lot of awesome training has been produced by Chris Fidao.  The training is **so** good, I really can't say enough.  He moves quickly through the topics so that you don't feel like you're wasting time, but not so fast that you can't keep up.

Here's some links that I highly recommend you check out if you're interested in learning more about docker, DevOps, Laravel and server management

- [Servers for Hackers](https://serversforhackers.com/)
- [Shipping Docker](https://serversforhackers.com/shipping-docker)
- [Vessel](https://vessel.shippingdocker.com/)

I created this project as an opportunity to get more familiar with the Docker workflow.  If you're interested in working on this project and helping to build in some other features, please check out the *Contributing* section below.  I'd love to work with you to help build some free tools!  Also, please send any suggestions if there's a feature you'd like to see.


## Installation

Note - after installation your app will be reachable on your host IP or localhost at the port specified in the `.env` file (port 8000 by default)

`http://<your_ip>:8000`

### Docker/Vessel
OS X & Linux:

```sh
git clone git@github.com:sloan58/cip3.git
cd cip3
cp .env.example .env
./vessel start
./vessel composer install
./vessel art key:generate
./vessel art migrate --seed
```

If you're using the app to push custom background images, you'll need to set the `APP_URL` in `.env`

```bash
APP_URL=http://<your.local.ip.address>:8000
```

And also link the `public/storage` directory

```bash
./vessel artisan storage:link
```

Windows:

*TODO*


### Without Docker/Vessel

If you're not using the Docker/Vessel integration, you'll need to have a development environment with PHP, composer and a database (and Redis, if you'd like) already installed.

OS X & Linux:

```sh
git clone git@github.com:sloan58/cip3.git
cd cip3
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
```
Be sure to configure your `.env` file to match your local resource configurations!

```bash
APP_URL=http://<your.local.ip.address>:8000
```

And also link the `public/storage` directory

```bash
./vessel artisan storage:link
```


Windows:

*TODO*

### Cisco UCM Setup
Create an account with the following Roles so that it can use the AXL and RisPort API's:

- AXL
- Standard CCM Server Monitoring

## Usage example

The `.env` file will have mostly everything set for the Docker/Vessel integration.  At the bottom of the file, you can modify the `APP_PORT` and `MYSQL_PORT` if you need to run them on another port so they don't conflict with a running app.  Please see the `Multiple Environments` section in the [vessel](https://github.com/shipping-docker/vessel) docs for more.

Once the app is running (and if you've seeded the database) you can login with:

`Username:` admin@cip3.com

`Password:` password123

### Screenshots

Check out the [screenshots](https://placeit) folder for some visuals

## Wish List
- UCM CURRI call blocking
- UCM CDR's
- UCM Audit Logs
- UCM PerfMon integration
- Call Recording
   
## Contributing

1. Fork it (<https://github.com/sloan58/cip3>)
2. Create your feature branch (`git checkout -b feature/fooBar`)
3. Commit your changes (`git commit -am 'Add some fooBar'`)
4. Push to the branch (`git push origin feature/fooBar`)
5. Create a new Pull Request
