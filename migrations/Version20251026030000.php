<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migración para agregar ON DELETE CASCADE a la relación product_image -> product
 * Esto permite que al eliminar un producto, sus imágenes se eliminen automáticamente
 */
final class Version20251026030000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Agregar ON DELETE CASCADE a la foreign key de product_image';
    }

    public function up(Schema $schema): void
    {
        // Eliminar la constraint actual sin CASCADE
        $this->addSql('ALTER TABLE product_image DROP FOREIGN KEY FK_64617F034584665A');

        // Volver a crear la constraint CON ON DELETE CASCADE
        $this->addSql('ALTER TABLE product_image ADD CONSTRAINT FK_64617F034584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Revertir: eliminar constraint con CASCADE
        $this->addSql('ALTER TABLE product_image DROP FOREIGN KEY FK_64617F034584665A');

        // Volver a crear constraint SIN CASCADE (estado original)
        $this->addSql('ALTER TABLE product_image ADD CONSTRAINT FK_64617F034584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }
}
