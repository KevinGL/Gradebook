<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250919185513 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appreciation (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, subject_id INT NOT NULL, text VARCHAR(255) NOT NULL, trimester INT NOT NULL, INDEX IDX_5CD4DEABCB944F1A (student_id), INDEX IDX_5CD4DEAB23EDC87 (subject_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appreciation ADD CONSTRAINT FK_5CD4DEABCB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE appreciation ADD CONSTRAINT FK_5CD4DEAB23EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appreciation DROP FOREIGN KEY FK_5CD4DEABCB944F1A');
        $this->addSql('ALTER TABLE appreciation DROP FOREIGN KEY FK_5CD4DEAB23EDC87');
        $this->addSql('DROP TABLE appreciation');
    }
}
