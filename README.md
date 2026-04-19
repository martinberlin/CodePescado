# CodePescado

Containers and web simple install using DDEV and OrbStack as container manager.

## Requirements 

- Create proper Doctrine migrations (not schema:update).

Use doctrine:migrations:migrate to create your tables

- PHP version used: 8.2 
- Add a Doctrine fixture or a console command to seed a test ApiClient with a known
  API key. 

      php bin/console app:seed-test-api-client

## Notification log 

Important: 
DateTime parameters need to be given in a way that can be converted from a string using 

Ex. of working queries:
/api/notifications?from=2026-01-06 00:00:00&to=2026-06-06 23:59:00&channel=email

/api/notifications?from=2026-01-06 00:00:00&to=now&channel=email

Also leaving **to** empty will take now as a default.

## Tables created using Doctrine ORM

    % ddev php bin/console doctrine:migrations:diff
Generated new migration class to "/var/www/html/migrations/Version20260417161656.php"

To run just this migration for testing purposes, you can use migrations:execute --up "DoctrineMigrations\\Version20260417161656"

To revert the migration you can use migrations:execute --down "DoctrineMigrations\\Version20260417161656"

Just run:

    % ddev php bin/console doctrine:migrations:migrate

to create the 2 tables for this example.

## Inspiration and self-education for this code example

https://medium.com/@laurentmn/symfony-compiler-passes-the-secret-weapon-youre-not-using-8c2699d6d6f2
