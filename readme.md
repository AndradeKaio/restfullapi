# Desafio RESTFul API

API desenvolvida em [Laravel 5.8](https://laravel.com/)

## Requirements

[Docker 19.03](https://docs.docker.com/)

ou

[PHP 7.2](https://www.php.net/download-docs.php)

[Laravel 5.8](https://laravel.com/docs/5.8/installation)

[Composer 1.9](https://getcomposer.org/download/)

## Installation

Instalação via git clone

```bash
git clone https://github.com/AndradeKaio/restfullapi.git
cd cars-api

```

## Usage

Usando docker (Download das imagens pode levar alguns minutos)

```bash
cd ~/cars-api
docker run --rm -v $(pwd):/app composer install
sudo chown -R $USER:$USER ~/cars-api
cp .env.example .env
docker compose-up -d
docker-compose exec app php artisan key:generate
```

Usando Laravel Server

```bash
cd ~/cars-api
composer install
composer update
chmod 755 app/storage
php artisan key:generate
php artisan serve
```

[https://localhost:8000](https://localhost:8000)
