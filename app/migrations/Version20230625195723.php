<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230625195723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE url_data ADD url_id INT NOT NULL');
        $this->addSql('ALTER TABLE url_data ADD CONSTRAINT FK_B3A73ADB81CFDAE7 FOREIGN KEY (url_id) REFERENCES urls (id)');
        $this->addSql('CREATE INDEX IDX_B3A73ADB81CFDAE7 ON url_data (url_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE url_data DROP FOREIGN KEY FK_B3A73ADB81CFDAE7');
        $this->addSql('DROP INDEX IDX_B3A73ADB81CFDAE7 ON url_data');
        $this->addSql('ALTER TABLE url_data DROP url_id');
    }
}
