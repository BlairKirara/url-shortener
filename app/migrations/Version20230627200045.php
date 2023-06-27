<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230627200045 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE urls_tags DROP FOREIGN KEY FK_87534E078D7B4FB4');
        $this->addSql('DROP INDEX IDX_87534E078D7B4FB4 ON urls_tags');
        $this->addSql('DROP INDEX `primary` ON urls_tags');
        $this->addSql('ALTER TABLE urls_tags CHANGE tags_id tag_id INT NOT NULL');
        $this->addSql('ALTER TABLE urls_tags ADD CONSTRAINT FK_87534E07BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_87534E07BAD26311 ON urls_tags (tag_id)');
        $this->addSql('ALTER TABLE urls_tags ADD PRIMARY KEY (url_id, tag_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE urls_tags DROP FOREIGN KEY FK_87534E07BAD26311');
        $this->addSql('DROP INDEX IDX_87534E07BAD26311 ON urls_tags');
        $this->addSql('DROP INDEX `PRIMARY` ON urls_tags');
        $this->addSql('ALTER TABLE urls_tags CHANGE tag_id tags_id INT NOT NULL');
        $this->addSql('ALTER TABLE urls_tags ADD CONSTRAINT FK_87534E078D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_87534E078D7B4FB4 ON urls_tags (tags_id)');
        $this->addSql('ALTER TABLE urls_tags ADD PRIMARY KEY (url_id, tags_id)');
    }
}
