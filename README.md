## Installation

Just copy files from this repository to your project main directory and update .env.dist

## Usage

 ```bash
 $ cp .env.dist .env
 ```


 ```bash
 $ /scripts/start-dev.sh
 ```
 
 ```bash
 $ scripts/backend.sh
 $ composer install
 ```
 
 
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