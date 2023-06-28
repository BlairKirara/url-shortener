<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230628075024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE url_data (id INT AUTO_INCREMENT NOT NULL, url_id INT NOT NULL, visit_time DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B3A73ADB81CFDAE7 (url_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE url_data ADD CONSTRAINT FK_B3A73ADB81CFDAE7 FOREIGN KEY (url_id) REFERENCES urls (id)');
        $this->addSql('ALTER TABLE urls_visited DROP FOREIGN KEY FK_B633420781CFDAE7');
        $this->addSql('DROP TABLE urls_visited');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE urls_visited (id INT AUTO_INCREMENT NOT NULL, url_id INT NOT NULL, visit_time DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B633420781CFDAE7 (url_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE urls_visited ADD CONSTRAINT FK_B633420781CFDAE7 FOREIGN KEY (url_id) REFERENCES urls (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE url_data DROP FOREIGN KEY FK_B3A73ADB81CFDAE7');
        $this->addSql('DROP TABLE url_data');
    }
}
