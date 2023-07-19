<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230719100022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added price history';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE price_history (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, price DOUBLE PRECISION NOT NULL, INDEX IDX_4C9CB817979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE price_history ADD CONSTRAINT FK_4C9CB817979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE price_history DROP FOREIGN KEY FK_4C9CB817979B1AD6');
        $this->addSql('DROP TABLE price_history');
    }
}
