<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251024001647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, categoria_id INT NOT NULL, create_by_id INT NOT NULL, sku VARCHAR(50) NOT NULL, nombre VARCHAR(180) NOT NULL, descripcion LONGTEXT DEFAULT NULL, precio NUMERIC(12, 2) NOT NULL, costo NUMERIC(12, 2) NOT NULL, stock INT NOT NULL, activo TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_D34A04ADF9038C4 (sku), INDEX IDX_D34A04AD3397707A (categoria_id), INDEX IDX_D34A04AD9E085865 (create_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD3397707A FOREIGN KEY (categoria_id) REFERENCES categoria (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD9E085865 FOREIGN KEY (create_by_id) REFERENCES user (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD3397707A');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD9E085865');
        $this->addSql('DROP TABLE product');
    }
}
