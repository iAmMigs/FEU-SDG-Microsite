<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260319074707 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity_sdg (activity_id INT NOT NULL, sdg_id INT NOT NULL, INDEX IDX_988CBC181C06096 (activity_id), INDEX IDX_988CBC16F37DCD9 (sdg_id), PRIMARY KEY (activity_id, sdg_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE activity_sdg ADD CONSTRAINT FK_988CBC181C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activity_sdg ADD CONSTRAINT FK_988CBC16F37DCD9 FOREIGN KEY (sdg_id) REFERENCES sdg (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activity ADD is_active TINYINT NOT NULL, ADD publish_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity_sdg DROP FOREIGN KEY FK_988CBC181C06096');
        $this->addSql('ALTER TABLE activity_sdg DROP FOREIGN KEY FK_988CBC16F37DCD9');
        $this->addSql('DROP TABLE activity_sdg');
        $this->addSql('ALTER TABLE activity DROP is_active, DROP publish_at');
    }
}
