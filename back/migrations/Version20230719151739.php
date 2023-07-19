<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230719151739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added cascade deleete';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lifecycle_iteration CHANGE date date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE price_history DROP FOREIGN KEY FK_4C9CB817712E810B');
        $this->addSql('ALTER TABLE price_history DROP FOREIGN KEY FK_4C9CB817979B1AD6');
        $this->addSql('ALTER TABLE price_history ADD CONSTRAINT FK_4C9CB817712E810B FOREIGN KEY (lifecycle_iteration_id) REFERENCES lifecycle_iteration (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE price_history ADD CONSTRAINT FK_4C9CB817979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lifecycle_iteration CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE price_history DROP FOREIGN KEY FK_4C9CB817979B1AD6');
        $this->addSql('ALTER TABLE price_history DROP FOREIGN KEY FK_4C9CB817712E810B');
        $this->addSql('ALTER TABLE price_history ADD CONSTRAINT FK_4C9CB817979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE price_history ADD CONSTRAINT FK_4C9CB817712E810B FOREIGN KEY (lifecycle_iteration_id) REFERENCES lifecycle_iteration (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
