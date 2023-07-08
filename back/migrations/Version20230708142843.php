<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230708142843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates share table and adds relationships';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE share (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, owner_id INT NOT NULL, INDEX IDX_EF069D5A979B1AD6 (company_id), INDEX IDX_EF069D5A7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A979B1AD6');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A7E3C61F9');
        $this->addSql('DROP TABLE share');
    }
}
