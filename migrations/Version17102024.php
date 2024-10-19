<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version17102024 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Create the products table if it doesn't exist, with created_at and updated_at fields
        $this->addSql("CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT NOT NULL, 
            sku VARCHAR(255) NOT NULL,
            price INT NOT NULL, 
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        )");

        // Create the bulk_price_rules table if it doesn't exist, with created_at and updated_at fields
        $this->addSql("CREATE TABLE IF NOT EXISTS bulk_price_rules (
            id INT AUTO_INCREMENT NOT NULL, 
            bulk_quantity INT NOT NULL, 
            bulk_price INT NOT NULL, 
            product_id INT DEFAULT NULL, 
            is_active TINYINT(1) DEFAULT 1 NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id), 
            CONSTRAINT FK_bulk_price_rules_products_id FOREIGN KEY (product_id) REFERENCES products (id)  -- Correct foreign key reference
        )");

        // Insert initial data into the products table
        $this->addSql("INSERT INTO products (sku, price, is_active, created_at, updated_at) VALUES 
            ('A', 50, 1, NOW(), NOW()),
            ('B', 30, 1, NOW(), NOW()),
            ('C', 20, 1, NOW(), NOW()),
            ('D', 10, 1, NOW(), NOW())");

        // Insert initial bulk price rules with created_at and updated_at
        $this->addSql("INSERT INTO bulk_price_rules (bulk_quantity, bulk_price, product_id, is_active, created_at, updated_at) VALUES 
            (3, 130, 1, 1, NOW(), NOW()),
            (3, 120, 1, 0, NOW(), NOW()),
            (2, 45, 2, 1, NOW(), NOW())");
    }

    public function down(Schema $schema): void
    {
        // Drop data in reverse order
        $this->addSql("DELETE FROM bulk_price_rules");
        $this->addSql("DELETE FROM products");

        // Optionally, you can drop tables here as well
        $this->addSql("DROP TABLE IF EXISTS bulk_price_rules");
        $this->addSql("DROP TABLE IF EXISTS products");
    }
}