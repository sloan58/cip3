# CIP<sup>3</sup>
> Cisco IP Phone Portal - track and manage your Cisco UC endpoints

CIP<sup>3</sup> is an open project to help Cisco Collaboration engineers track and manage devices within their Cisco UCM clusters.

It uses the PHP Laravel framework and contains a Docker workflow to help you get up and running fast.

The current version offers the following:

- Cisco UCM Server(s) CRUD interface
- Sync Cisco UCM Phones/Devices and RisPort data daily and on-demand
- Sync History
- Sync success/fail notifications via Webex Teams BYOB (Bring Your Own Bot)
- Display device information and real-time data (IP Address, Firmware, etc) 
- Manage User accounts

Here's some of the packages included:

[Laravel 5.8](www.laravel.com) - the core application framework

[Laravel Telescope](https://laravel.com/docs/5.8/telescope) - an elegant debug assistant for the Laravel framework

[Laravel Backpack](https://laravel-backpack.readme.io/docs) - for rapid UI prototyping

[laravel-chartjs](https://github.com/fxcosta/laravel-chartjs) - build sweet looking charts in your Laravel controllers

[Vessel](https://vessel.shippingdocker.com/) - a Docker dev environment for Laravel

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

Below are some sample screenshots after syncing my lab system.

### Screenshots

#### Dashboard
![image](https://user-images.githubusercontent.com/6303820/59216094-e4af0800-8b88-11e9-919c-086ee2f6d62f.png)

#### UCM List
![image](https://user-images.githubusercontent.com/6303820/59216141-014b4000-8b89-11e9-9e9f-72ea60d524d1.png)

#### Sync Details
![image](https://user-images.githubusercontent.com/6303820/59388754-993c5b80-8d3a-11e9-8464-7d573ffe03f8.png)


#### UCM Add/Edit
![image](https://user-images.githubusercontent.com/6303820/59216225-35266580-8b89-11e9-89d6-014ce270a37a.png)

#### Phone/Device Details
![image](https://user-images.githubusercontent.com/6303820/59225219-25fde280-8b9e-11e9-936b-12e91ac0c444.png)

#### Webex Teams Integration
![image](https://user-images.githubusercontent.com/6303820/59388678-65f9cc80-8d3a-11e9-9f56-60a1f3d21084.png)

![image](https://user-images.githubusercontent.com/6303820/59388708-78740600-8d3a-11e9-8b2a-3da2f6b992c1.png)


## Todo

- Create some roles and permissions
- Create a docker-compose file that pulls in the app, rather than the app pulling in Docker
- LDAP integration - I've used the [adldap2 package](https://github.com/Adldap2/Adldap2) before, which works nicely.
- Tests, for goodness' sake!

## New Features / Wish List
- UCM CURRI Integration for selective call blocking - I have code if anyone wants to help integrate.
- UCM CDR's - import CDR's into CIP<sup>3</sup> and attach them to the devices for search/visibility.  I have a bash script to import into MySQL but an Elasticsearch container would be great!
- UCM Audit Logs - Correlate audit log events with devices in CIP<sup>3</sup> (Elasticsearch again?)
- Custom IP Phone Background images - I have code for this to get started.  It accepts an uploaded file and converts the image to the necessary formats for a list of IP Phones (and the thumbnails), then pushes them out using the `setBackground` API
- UCM PerfMon integration - I have some starter code that syncs PerfMon Objects and Classes.  Multi-cluster DB replication status perhaps?
- Call Recording - I have call recording with Asterisk working as a POC, but it needs some significant work to make it basically useful.  I can share my Asterisk `extensions.conf` and `sip.conf`.  This is pretty ambitious but would be awesome to have an open-source recording solution for UCM!
   
## Contributing

1. Fork it (<https://github.com/sloan58/cip3>)
2. Create your feature branch (`git checkout -b feature/fooBar`)
3. Commit your changes (`git commit -am 'Add some fooBar'`)
4. Push to the branch (`git push origin feature/fooBar`)
5. Create a new Pull Request
