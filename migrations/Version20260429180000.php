<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260429180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create products + orders tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE products (
                id UUID NOT NULL,
                name VARCHAR(255) NOT NULL,
                price NUMERIC(10, 2) NOT NULL,
                quantity INT NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql("COMMENT ON COLUMN products.id IS '(DC2Type:uuid)'");

        $this->addSql(<<<'SQL'
            CREATE TABLE orders (
                id UUID NOT NULL,
                product_id UUID NOT NULL,
                customer_name VARCHAR(255) NOT NULL,
                quantity_ordered INT NOT NULL,
                status VARCHAR(32) NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql("COMMENT ON COLUMN orders.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN orders.product_id IS '(DC2Type:uuid)'");
        $this->addSql('CREATE INDEX idx_orders_product ON orders (product_id)');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT fk_orders_product FOREIGN KEY (product_id) REFERENCES products (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE products');
    }
}
