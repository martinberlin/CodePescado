<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260417161656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE api_client (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, api_key VARCHAR(64) NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX uniq_api_client_api_key (api_key), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notification_log (id INT AUTO_INCREMENT NOT NULL, channel VARCHAR(50) NOT NULL, subject VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, status VARCHAR(20) NOT NULL, error_message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE api_client');
        $this->addSql('DROP TABLE notification_log');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
