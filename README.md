# CodePescado

Containers and web simple install using DDEV and OrbStack as container manager.

## Requirements 

- Create proper Doctrine migrations (not schema:update).

Use doctrine:migrations:migrate to create your tables

- PHP version used: 8.2 
- Symfony version 7.4.8

## Installation

    git clone https://github.com/martinberlin/CodePescado.git
    cd CodePescado
    // NOTE: Used PHP 8.2 as specified with ddev change this php for your version in command line
    php composer install
    // Create a db in mysql called 'db' and update DATABASE_URL in the environment
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    // Please use this command to seed the api-client and insert it on the DB:
    php bin/console app:seed-test-api-client

Needs the [symfony CLI installed](https://symfony.com/download)

    symfony server:start

Run some tests:

    php bin/phpunit --testdox

## Notification log 

Important: 
DateTime parameters need to be given in a way that can be converted from a string using 

Ex. of working queries:
/api/notifications?from=2026-01-06 00:00:00&to=2026-06-06 23:59:00&channel=email

/api/notifications?from=2026-01-06 00:00:00&to=now&channel=email

/api/notifications?from=2026-01-06 00:00:XX&to=now&channel=email will drop a 422 error response

## Inspiration and self-education for this code example

https://medium.com/@laurentmn/symfony-compiler-passes-the-secret-weapon-youre-not-using-8c2699d6d6f2
