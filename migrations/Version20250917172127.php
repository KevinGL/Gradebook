<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250917172127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE school_class (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, year VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE school_class_user (school_class_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_8C4F61F414463F54 (school_class_id), INDEX IDX_8C4F61F4A76ED395 (user_id), PRIMARY KEY(school_class_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subject (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE school_class_user ADD CONSTRAINT FK_8C4F61F414463F54 FOREIGN KEY (school_class_id) REFERENCES school_class (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE school_class_user ADD CONSTRAINT FK_8C4F61F4A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD class_id INT DEFAULT NULL, ADD subject_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649EA000B10 FOREIGN KEY (class_id) REFERENCES school_class (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64923EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649EA000B10 ON user (class_id)');
        $this->addSql('CREATE INDEX IDX_8D93D64923EDC87 ON user (subject_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649EA000B10');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D64923EDC87');
        $this->addSql('ALTER TABLE school_class_user DROP FOREIGN KEY FK_8C4F61F414463F54');
        $this->addSql('ALTER TABLE school_class_user DROP FOREIGN KEY FK_8C4F61F4A76ED395');
        $this->addSql('DROP TABLE school_class');
        $this->addSql('DROP TABLE school_class_user');
        $this->addSql('DROP TABLE subject');
        $this->addSql('DROP INDEX IDX_8D93D649EA000B10 ON `user`');
        $this->addSql('DROP INDEX IDX_8D93D64923EDC87 ON `user`');
        $this->addSql('ALTER TABLE `user` DROP class_id, DROP subject_id');
    }
}
