# CodePescado

Containers and web simple install using DDEV and OrbStack as container orchestator.


# Tables created using Doctrine ORM

    % ddev php bin/console doctrine:migrations:diff
Generated new migration class to "/var/www/html/migrations/Version20260417161656.php"

To run just this migration for testing purposes, you can use migrations:execute --up "DoctrineMigrations\\Version20260417161656"

To revert the migration you can use migrations:execute --down "DoctrineMigrations\\Version20260417161656"

Just run:

    % ddev php bin/console doctrine:migrations:migrate

to create the 2 tables for this example.
