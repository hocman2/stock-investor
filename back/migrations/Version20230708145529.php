<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230708145529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates company domain table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company_domain (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE company ADD domain_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F115F0EE5 FOREIGN KEY (domain_id) REFERENCES company_domain (id)');
        $this->addSql('CREATE INDEX IDX_4FBF094F115F0EE5 ON company (domain_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F115F0EE5');
        $this->addSql('DROP TABLE company_domain');
        $this->addSql('DROP INDEX IDX_4FBF094F115F0EE5 ON company');
        $this->addSql('ALTER TABLE company DROP domain_id');
    }
}
