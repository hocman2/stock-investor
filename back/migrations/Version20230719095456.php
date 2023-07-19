<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230719095456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Edited foreign key constraints; added trend field for companies';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F115F0EE5');
        $this->addSql('ALTER TABLE company ADD trend DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F115F0EE5 FOREIGN KEY (domain_id) REFERENCES company_domain (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A7E3C61F9');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A979B1AD6');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094F115F0EE5');
        $this->addSql('ALTER TABLE company DROP trend');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094F115F0EE5 FOREIGN KEY (domain_id) REFERENCES company_domain (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A979B1AD6');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A7E3C61F9');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
