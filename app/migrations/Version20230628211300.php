<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230628211300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE guest_users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(191) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(70) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE url_data (id INT AUTO_INCREMENT NOT NULL, url_id INT NOT NULL, visit_time DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B3A73ADB81CFDAE7 (url_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE urls (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, guest_user_id INT DEFAULT NULL, long_name VARCHAR(255) NOT NULL, short_name VARCHAR(255) NOT NULL, create_time DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_blocked TINYINT(1) NOT NULL, block_time DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2A9437A1A76ED395 (user_id), INDEX IDX_2A9437A1E7AB17D9 (guest_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE urls_tags (url_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_87534E0781CFDAE7 (url_id), INDEX IDX_87534E07BAD26311 (tag_id), PRIMARY KEY(url_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(191) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX email_idx (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE url_data ADD CONSTRAINT FK_B3A73ADB81CFDAE7 FOREIGN KEY (url_id) REFERENCES urls (id)');
        $this->addSql('ALTER TABLE urls ADD CONSTRAINT FK_2A9437A1A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE urls ADD CONSTRAINT FK_2A9437A1E7AB17D9 FOREIGN KEY (guest_user_id) REFERENCES guest_users (id)');
        $this->addSql('ALTER TABLE urls_tags ADD CONSTRAINT FK_87534E0781CFDAE7 FOREIGN KEY (url_id) REFERENCES urls (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE urls_tags ADD CONSTRAINT FK_87534E07BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE url_data DROP FOREIGN KEY FK_B3A73ADB81CFDAE7');
        $this->addSql('ALTER TABLE urls DROP FOREIGN KEY FK_2A9437A1A76ED395');
        $this->addSql('ALTER TABLE urls DROP FOREIGN KEY FK_2A9437A1E7AB17D9');
        $this->addSql('ALTER TABLE urls_tags DROP FOREIGN KEY FK_87534E0781CFDAE7');
        $this->addSql('ALTER TABLE urls_tags DROP FOREIGN KEY FK_87534E07BAD26311');
        $this->addSql('DROP TABLE guest_users');
        $this->addSql('DROP TABLE tags');
        $this->addSql('DROP TABLE url_data');
        $this->addSql('DROP TABLE urls');
        $this->addSql('DROP TABLE urls_tags');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
