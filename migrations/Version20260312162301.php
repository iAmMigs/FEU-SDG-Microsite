<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260312162301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, category VARCHAR(100) NOT NULL, content LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, event_date VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sdg_goal (id INT AUTO_INCREMENT NOT NULL, goal_number INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE thesis (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, authors VARCHAR(255) NOT NULL, views INT DEFAULT 0 NOT NULL, cover_image VARCHAR(255) DEFAULT NULL, document_file VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE thesis_sdg_goal (thesis_id INT NOT NULL, sdg_goal_id INT NOT NULL, INDEX IDX_107FA2B068D82738 (thesis_id), INDEX IDX_107FA2B09B5EE993 (sdg_goal_id), PRIMARY KEY (thesis_id, sdg_goal_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE thesis_sdg_goal ADD CONSTRAINT FK_107FA2B068D82738 FOREIGN KEY (thesis_id) REFERENCES thesis (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE thesis_sdg_goal ADD CONSTRAINT FK_107FA2B09B5EE993 FOREIGN KEY (sdg_goal_id) REFERENCES sdg_goal (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE thesis_sdg_goal DROP FOREIGN KEY FK_107FA2B068D82738');
        $this->addSql('ALTER TABLE thesis_sdg_goal DROP FOREIGN KEY FK_107FA2B09B5EE993');
        $this->addSql('DROP TABLE activity');
        $this->addSql('DROP TABLE sdg_goal');
        $this->addSql('DROP TABLE thesis');
        $this->addSql('DROP TABLE thesis_sdg_goal');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
