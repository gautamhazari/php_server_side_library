GSMA MobileConnect PHP Server Side Library
==============================================================================================================

Mobile Connect is a mobile identity service based on the OpenID Connect & OAuth2 where end users can authenticate themselves using their mobile phone via Mobile Connect. This allows them access to websites and applications without the need to remember passwords and usernames. It’s safe, secure and no personal information is shared without their permission.

Note: if you operate in the EU then you should use EU Discovery Service domain in discovery URL: eu.discover.mobileconnect.io

## Quick Start
Install Docker.
```posh
sudo apt install docker.io
systemctl start docker
systemctl enable docker
```
Create at host machine folder <code class="java-lang">/home/serverside/</code> for example and go into it.
Download the Mobile Connect server side project.
```posh
git init
git pull https://github.com/Mobile-Connect/php_server_side_library.git
```
Run docker image:
```posh
sudo docker run -p 80:80 -it -v /home/serverside/:/opt/lampp/htdocs/ cswl/xampp bash
```
In Docker container run next commands:
```posh
cd /opt/lampp/htdocs/php-server-side-library
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
composer install
mv .env.example .env
php artisan key:generate
chmod -R 777 /opt/lampp/htdocs/php-server-side-library/
chmod -R 777 /opt/lampp/htdocs/mobile-connect-sdk/
mysql</opt/lampp/htdocs/init_db.sql 
```
Note: please use your actual path to PHP server side library in Docker image instead of /opt/lampp/htdocs/ if it is different.
Open the configuration file and change it: \php-server-side-library\app\data\data.json.
Here are 10 parameters:
```posh
  {
  “msisdn”: your msisdn
  "clientID": your client Id,
  "clientSecret": your client Secret,
  "discoveryURL": your Discovery endpoint,
  "redirectURL": "http://your redirect url",
  "xRedirect": "True",
  "includeRequestIP": "False",
  "apiVersion": api version: "mc_v1.1" or "mc_v1.2",
  "scopes": scopes,
  "clientName":  your client name
}
```
Installation is finished.
You can open the configuration file <code class="java-lang">.env</code> at the root of the project and set correct values for variables DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME and DB_PASSWORD. 
After changing configuration files you have to restart the docker container:
```posh
sudo docker run -p 80:80 -it -v /home/serverside/:/opt/lampp/htdocs/ cswl/xampp bash
mysql</opt/lampp/htdocs/init_db.sql 
```

## Support

If you encounter any issues which are not resolved by consulting the resources below then [send us a message](https://developer.mobileconnect.io/content/contact-us)

## Resources

- [MobileConnect Discovery API Information](https://developer.mobileconnect.io/discovery-api)
- [MobileConnect Authentication API Information](https://developer.mobileconnect.io/mobile-connect-api)
- [MobileConnect Authentication API (v2.0) Information](https://developer.mobileconnect.io/mobile-connect-profile-v2-0)

