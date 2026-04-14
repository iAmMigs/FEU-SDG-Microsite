<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260413152431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE college (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE project_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE thesis ADD doi VARCHAR(255) DEFAULT NULL, ADD type_id INT DEFAULT NULL, ADD college_id INT DEFAULT NULL, DROP type, DROP college');
        $this->addSql('ALTER TABLE thesis ADD CONSTRAINT FK_AF4FF3A8C54C8C93 FOREIGN KEY (type_id) REFERENCES project_type (id)');
        $this->addSql('ALTER TABLE thesis ADD CONSTRAINT FK_AF4FF3A8770124B2 FOREIGN KEY (college_id) REFERENCES college (id)');
        $this->addSql('CREATE INDEX IDX_AF4FF3A8C54C8C93 ON thesis (type_id)');
        $this->addSql('CREATE INDEX IDX_AF4FF3A8770124B2 ON thesis (college_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE college');
        $this->addSql('DROP TABLE project_type');
        $this->addSql('ALTER TABLE thesis DROP FOREIGN KEY FK_AF4FF3A8C54C8C93');
        $this->addSql('ALTER TABLE thesis DROP FOREIGN KEY FK_AF4FF3A8770124B2');
        $this->addSql('DROP INDEX IDX_AF4FF3A8C54C8C93 ON thesis');
        $this->addSql('DROP INDEX IDX_AF4FF3A8770124B2 ON thesis');
        $this->addSql('ALTER TABLE thesis ADD college VARCHAR(255) DEFAULT NULL, DROP type_id, DROP college_id, CHANGE doi type VARCHAR(255) DEFAULT NULL');
    }
}
