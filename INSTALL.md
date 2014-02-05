# Installing BeeHub on your own server

## Prerequisites

BeeHub is written in PHP and intended/tested in a Linux environment with Apache 2 as webserver. You require the following software to install and run a BeeHub server:

* Apache 2.2 or higher
* Apache modules: mod_rewrite and mod_ssl
* PHP 5.3 or higher, both the CLI and the Apache2 interface
* PHP extensions: fileinfo, mbstring, mysqli, session and pcntl. And from the PECL repository: xattr.
* mySQL 5 or higher
* git
* make
* simpleSAMLphp; see http://simplesamlphp.org/
* Composer.phar; a dependency manager for PHP, see https://getcomposer.org
* Litmus webDAV test suite (compiled with ssl support); see http://www.webdav.org/neon/litmus/

### Requirements by dependencies:

At the moment of writing, js-webdav-lib requires the following:

* java
* unzip

One or more dependencies installed by Composer also require PHP extensions. At the moment of writing, most noticeable:

* dom
* xsl
 
### e.g.: In Ubuntu 12.04

```
$ sudo aptitude install apache2 php5 libapache2-mod-php5 php-pear php5-dev libmagic-dev php5-mysql php5-gd php5-mcrypt php5-xsl mysql-server git make simplesamlphp default-jre unzip 

$ curl -sS https://getcomposer.org/installer | php
$ sudo mv composer.phar /usr/local/bin/composer

$ wget http://www.webdav.org/neon/litmus/litmus-0.13.tar.gz
$ tar -zxf litmus-0.13.tar.gz
$ cd litmus-0.13
$ ./configure
$ make
$ sudo make install
```

## Installing BeeHub

To install BeeHub, you can simply run 'make install' and the installation script will notice any missing dependencies and require you to enter some information on the environment. However, following these steps will ease the installation process:

1. Install all dependencies as mentioned above. Most stuff can be installed through your distro's package manager. Composer and litmus will probably have to be installed manually, make sure the binaries are in a directory in your PATH environment variable.
2. Create a mySQL user and database.
3. Create a data directory on an XFS partition.
4. If you haven't done this yet, (git) clone the BeeHub repository and optionally checkout the branch you want to use.
5. (Download and) configure simpleSAMLphp. Note that BeeHub is created with SURFconext in mind (see http://www.surf.nl/diensten-en-producten/surfconext/index.html). Possibly you could configure simpleSAMLphp to work with different identity providers too. BeeHub requires that there is an 'authsource' called 'BeeHub' which requires the following attributes to be returned:
   - urn:mace:dir:attribute-def:mail
   - urn:mace:dir:attribute-def:displayName
   - urn:mace:terena.org:attribute-def:schacHomeOrganization
   - urn:mace:dir:attribute-def:eduPersonAffiliation
6. Configure Apache:
   - Use $(pwd)public/ as document root
   - "AccessFileName .htaccess" and "AllowOverride All" for the document root, or copy the directives in /public/.htaccess into the Directory section of the central Apache configuration
   - Listen for HTTP connections (preferably on port 80)
   - Listen for HTTPS connections (preferably on port 443, but always 363 ports after the HTTP port)
   - Apache has write access to the data directory
   - Apache has write access to /public/system/js/server/principals.js
7. Run 'make install'. If the installation fails, fix te indicated problem and just run 'make install' again.

## Testing the installation

To test your installation, simply run:

```
make test
```

IMPORTANT: You need a separate test configuration. If it is not present yet, you will be asked some questions to create this. It is important to create a separate database (and preferably mySQL user) and data directory for your tests. Running the tests will erase all data in the database and the datadir!
