<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230507083340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_variant DROP FOREIGN KEY FK_209AA41DDE18E50B');
        $this->addSql('DROP INDEX IDX_209AA41DDE18E50B ON product_variant');
        $this->addSql('ALTER TABLE product_variant CHANGE product_id_id product_id INT NOT NULL');
        $this->addSql('ALTER TABLE product_variant ADD CONSTRAINT FK_209AA41D4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_209AA41D4584665A ON product_variant (product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_variant DROP FOREIGN KEY FK_209AA41D4584665A');
        $this->addSql('DROP INDEX IDX_209AA41D4584665A ON product_variant');
        $this->addSql('ALTER TABLE product_variant CHANGE product_id product_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE product_variant ADD CONSTRAINT FK_209AA41DDE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_209AA41DDE18E50B ON product_variant (product_id_id)');
    }
}
