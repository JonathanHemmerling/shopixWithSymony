<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230201121229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorys (id INT AUTO_INCREMENT NOT NULL, category_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attributes ADD attribute_name1 VARCHAR(255) NOT NULL, ADD attribute_name2 VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE main_categorys DROP display_name');
        $this->addSql('ALTER TABLE products CHANGE price price INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE categorys');
        $this->addSql('ALTER TABLE attributes DROP attribute_name1, DROP attribute_name2');
        $this->addSql('ALTER TABLE products CHANGE price price DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE main_categorys ADD display_name VARCHAR(255) NOT NULL');
    }
}
