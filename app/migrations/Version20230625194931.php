<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230625194931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE urls DROP FOREIGN KEY FK_2A9437A18A86F760');
        $this->addSql('DROP INDEX IDX_2A9437A18A86F760 ON urls');
        $this->addSql('ALTER TABLE urls DROP url_data_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE urls ADD url_data_id INT NOT NULL');
        $this->addSql('ALTER TABLE urls ADD CONSTRAINT FK_2A9437A18A86F760 FOREIGN KEY (url_data_id) REFERENCES url_data (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_2A9437A18A86F760 ON urls (url_data_id)');
    }
}
