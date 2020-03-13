## Specifications

- Php 7.2
- NodeJs 10.15.2
- Yarn 1.22.4

## Installation

Just copy files from this repository to your project main directory and update .env.dist

## Usage

 ```bash
 $ cp .env.dist .env
 ```

Run docker containers:

 ```bash
 $ /scripts/start-dev.sh
 ```
 
For windows:

(if needed, make executable files in scripts folder)

```
scripts\start-dev.sh
```

Go inside container:
 
 ```bash
 $ docker-compose -f docker-compose.yml exec php bash
 ```
 
Install dependencies:
 
 ```
 composer install
 ```
 
Install yarn:
 
```
yarn install
```

Install assets:

```
yarn run dev
```

Create database and run migrations:

```
php bin/console doctrine:database:create
```
```
php bin/console doctrine:migrations:migrate
```

## Messenger

Run worker:

```
php bin/console messenger:consume -vv
``` 

## Deploy

- ``heroku create``
- ``echo 'web: vendor/bin/heroku-php-nginx -C nginx_app.conf public/' > Procfile`` and commit to repository
- ``heroku config:set APP_ENV=prod`` set production environment
- ``heroku git:remote -a {app-name}``
- ``heroku apps`` check your apps
- In heroku page setup Buildpacks in order: ``heroku/nodejs``, ``heroku/php``, ``https://github.com/kreativgebiet/heroku-buildpack-webpack``
- Add database: JawsDB.
- Create database and run migrations.

 
 
 ## (Tips) Use this code in your repository
 
```bash
$ rm -r .git
$ git init
$ git remote add origin {git@bitbucket.org:your_repository}
$ git add . 
$ git push -u origin master
```

## Useful commands

### View specific container logs
```bash
$ docker ps -a
$ docker logs CONTAINER_ID
```


### bash commands
```bash
$ docker-compose -f docker-compose.yml exec php bash
```

### Composer (e.g. composer install)
```bash
$ docker-compose -f docker-compose.yml exec php composer install
```

### MySQL commands 
```bash
$ docker-compose -f docker-compose.yml exec db mysql -uroot -p"root"
```
### Check CPU consumption
```bash
$ docker stats $(docker inspect -f "{{ .Name }}" $(docker ps -q))
```
### Delete all containers
```bash
$ docker rm $(docker ps -aq)
```

### Delete all images
```bash
$ docker rmi $(docker images -q)
```