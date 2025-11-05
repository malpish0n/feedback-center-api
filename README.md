![Build](https://github.com/malpish0n/feedbackcenter/actions/workflows/symfony.yml/badge.svg)

# FeedbackCenter API

### Authors:

Arkadiusz Kasztelan

Wojciech Jakubiak

## Technologies used:

    Docker
    
    Symfony
    
    Symfony Security
    
    Doctrine ORM

    LexikJWTAuthenticationBundle

    Postman (or any API client) for testing

## Features:

    User registration and login

    JWT token-based authentication

    Role-based access control (User/Admin)

    Management of Posts, users and groups

    Consistent JSON responses formatted by ApiResponseFormatter

## Requirements

    PHP 8.1 or higher

    Composer

    Symfony CLI
    
    Docker

# Installation and Setup

## 1. Clone the repository
git clone https://github.com/malpish0n/feedback-center-api.git

cd FeedbackCenter

## 2. Start Docker containers (build and run)
docker compose up -d --build

## 3. Access the PHP container
docker exec -it feedbackcenter-php-1 bash

## 4. Install PHP dependencies
composer install

## 5. Run database migrations

php bin/console make:migrations

php bin/console doctrine:migrations:migrate

## JWT Key Generation

If keys are not generated yet, run:

mkdir -p config/jwt

openssl genrsa -out config/jwt/private.pem -aes256 4096

openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

## Then, configure .env by adding this:

JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem

JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem

JWT_PASSPHRASE= (your passhprase goes here)

## Fixtures loading

php bin/console doctrine:fixtures:load

## Use the JWT token in the Authorization header for all protected endpoints:

Authorization: Bearer <your_token>

| Endpoint        | Method | Access        | Description              |
| --------------- | ------ | ------------- | ------------------------ |
| /api/posts      | GET    | Public        | Get all posts            |
| /api/posts/{id} | GET    | Public        | Get single post          |
| /api/posts      | POST   | Authenticated | Add new post             |
| /api/posts/{id} | PUT    | Post Author   | Edit post                |
| /api/posts/{id} | DELETE | Post Author   | Remove post              |
| /api/profile    | GET    | Authenticated | Get current user profile |
| /api/users/edit | PUT    | Authenticated | Edit current user        |
| /api/register   | POST   | Public        | Register a new user      |
| /api/login      | POST   | Public        | Login and get JWT token  |
| /api/logout     | POST   | Authenticated | Log out                  |

## Roles and Permissions

    ROLE_USER — default user role

    ROLE_ADMIN — full privileges

## Testing with Postman or Similar

    Log in via /api/login to get your JWT token

    Include token in Authorization header for protected routes

## Postman Collection

You can import the Postman collection located at `FeedbackCenter/docs/postman/` to test the API easily.

Steps to import in Postman:

1. Open Postman.
2. Click **Import** in the top-left corner.
3. Select **File** tab.
4. Choose the `FeedbackCenter API.postman_collection.json` file.
5. Click **Import**.


Secrets and private keys have been removed for security reasons. The project works with appropriate keys and passphrases.

