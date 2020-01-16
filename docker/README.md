# Docker Symfony (PHP7.2-FPM - NGINX - MySQL - ELK)


Docker-symfony gives you everything you need to develop Symfony applications. This complete stack run with docker and [docker-compose (1.7 or higher)](https://docs.docker.com/compose/).

Executing the Docker Command Without Sudo [here]( https://docs.docker.com/install/linux/linux-postinstall/#manage-docker-as-a-non-root-user) (recommended).

## Installation

Just copy files from this repository to your project main directory and update .env.dist

## Usage

1. Create a `.env` from the `.env.dist` file. Adapt it according to your symfony application

    ```bash
    cp .env.dist .env
    ```
2. Run `sysctl -w vm.max_map_count=262144` with root permissions (only if using elastic search container)
3. Build/run containers

    ```bash
    $ /scripts/start-dev.sh
    ```

4. Prepare Symfony app
    1. Update app/config/parameters.yml 

        ```yml
        # path/to/your/symfony-project/app/config/parameters.yml
        parameters:
            database_host: db
        ```

    2. Composer install & create database

        ```bash
        $ scripts/backend.sh
        $ composer install
        # Symfony3
        $ sf doctrine:database:create
        $ sf doctrine:schema:update --force
        # Only if you have `doctrine/doctrine-fixtures-bundle` installed
        $ sf doctrine:fixtures:load --no-interaction
        ```

5. Enjoy :-)

* Symfony app: visit [127.0.0.1:8080](http://symfony.dev:8080)  
* Symfony dev mode: visit [127.0.0.1:8080/app_dev.php](http://symfony.dev:8080/app_dev.php)  
* Logs (Kibana): [127.0.0.1:81](http://symfony.dev:81)
* Logs (files location): logs/nginx and logs/symfony

## Customize

If you want to add optional containers like Redis, PHPMyAdmin... take a look on [doc/custom.md](doc/custom.md).

## How it works?

Have a look at the `docker-compose.yml` file, here are the `docker-compose` built images:

* `db`: This is the MySQL database container,
* `php`: This is the PHP-FPM container in which the application volume is mounted,
* `nginx`: This is the Nginx webserver container in which application volume is mounted too,
* `elk`: This is a ELK stack container which uses Logstash to collect logs, send them into Elasticsearch and visualize them with Kibana.

This results in the following running containers:

```bash
$ docker-compose ps
           Name                          Command               State              Ports            
--------------------------------------------------------------------------------------------------
dockersymfony_db_1            /entrypoint.sh mysqld            Up      0.0.0.0:3307->3306/tcp      
dockersymfony_elk_1           /usr/bin/supervisord -n -c ...   Up      0.0.0.0:81->80/tcp          
dockersymfony_nginx_1         nginx                            Up      443/tcp, 0.0.0.0:8080->80/tcp
dockersymfony_php_1           php-fpm                          Up      0.0.0.0:9000->9000/tcp      
```

## Useful commands

```bash
# View specific container logs
$ docker ps -a
$ docker logs CONTAINER_ID

# bash commands
$ docker-compose -f ./docker/docker-compose.yml exec php bash

# Composer (e.g. composer update)
$ docker-compose -f ./docker/docker-compose.yml exec php composer update

# SF commands (Tips: there is an alias inside php container)
$ docker-compose -f ./docker/docker-compose.yml exec php php /var/www/symfony/bin/console cache:clear # Symfony3
# Same command by using alias
$ docker-compose -f ./docker/docker-compose.yml exec php bash
$ sf cache:clear

# Retrieve an IP Address (here for the nginx container)
$ docker inspect --format '{{ .NetworkSettings.Networks.dockersymfony_default.IPAddress }}' $(docker ps -f name=nginx -q)
$ docker inspect $(docker ps -f name=nginx -q) | grep IPAddress

# MySQL commands
$ docker-compose -f ./docker/docker-compose.yml exec db mysql -uroot -p"root"

# Check CPU consumption
$ docker stats $(docker inspect -f "{{ .Name }}" $(docker ps -q))

# Delete all containers
$ docker rm $(docker ps -aq)

# Delete all images
$ docker rmi $(docker images -q)
```

## FAQ

* Permission problem? See [this doc (Setting up Permission)](http://symfony.com/doc/current/book/installation.html#checking-symfony-application-configuration-and-setup)