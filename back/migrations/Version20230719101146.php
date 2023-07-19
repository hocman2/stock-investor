<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230719101146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added lifecycle iteration table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lifecycle_iteration (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE price_history ADD lifecycle_iteration_id INT NOT NULL');
        $this->addSql('ALTER TABLE price_history ADD CONSTRAINT FK_4C9CB817712E810B FOREIGN KEY (lifecycle_iteration_id) REFERENCES lifecycle_iteration (id)');
        $this->addSql('CREATE INDEX IDX_4C9CB817712E810B ON price_history (lifecycle_iteration_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE price_history DROP FOREIGN KEY FK_4C9CB817712E810B');
        $this->addSql('DROP TABLE lifecycle_iteration');
        $this->addSql('DROP INDEX IDX_4C9CB817712E810B ON price_history');
        $this->addSql('ALTER TABLE price_history DROP lifecycle_iteration_id');
    }
}
