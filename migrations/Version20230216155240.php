<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230216155240 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE blog (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, slug VARCHAR(255) NOT NULL, seo_title VARCHAR(255) NOT NULL, seo_description LONGTEXT DEFAULT NULL, is_comments_allowed TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_C0155143989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blog_article (id INT UNSIGNED AUTO_INCREMENT NOT NULL, blog_id INT UNSIGNED NOT NULL, image_id INT UNSIGNED DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, enabled TINYINT(1) DEFAULT \'0\' NOT NULL, excerpt LONGTEXT DEFAULT NULL, author VARCHAR(255) DEFAULT NULL, seo_title VARCHAR(255) NOT NULL, seo_description LONGTEXT DEFAULT NULL, slug VARCHAR(255) NOT NULL, published_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_EECCB3E5989D9B62 (slug), INDEX IDX_EECCB3E5DAE07E97 (blog_id), UNIQUE INDEX UNIQ_EECCB3E53DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blog_article ADD CONSTRAINT FK_EECCB3E5DAE07E97 FOREIGN KEY (blog_id) REFERENCES blog (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_article ADD CONSTRAINT FK_EECCB3E53DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
        $this->addSql('');
        $blogs = [
            [
                'name' => 'Blog',
                'seo_title' => 'Blog',
                'slug' => 'blog'
            ],
        ];
        foreach ($blogs as $item) {
            $this->addSql('INSERT INTO blog (name, seo_title, slug) VALUES (:name, :seo_title, :slug )', $item);
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE blog_article DROP FOREIGN KEY FK_EECCB3E5DAE07E97');
        $this->addSql('DROP TABLE blog');
        $this->addSql('DROP TABLE blog_article');
    }
}
