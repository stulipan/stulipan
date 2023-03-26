<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230204221542 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('CREATE TABLE host (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, language_code VARCHAR(2) NOT NULL, country_code VARCHAR(2) NOT NULL, enabled TINYINT(1) DEFAULT \'1\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $hosts = [
            [
                'name' => 'stulipan.dfr',
                'language_code' => 'hu',
                'country_code' => 'HU'
            ],
            [
                'name' => 'stulipan.com',
                'language_code' => 'en',
                'country_code' => 'EN'
            ],
        ];
        foreach ($hosts as $host) {
            $this->addSql('INSERT INTO host (name, language_code, country_code) VALUES (:name, :language_code, :country_code )', $host);
        }
        $this->addSql('ALTER TABLE myuser CHANGE verified_email verified_email TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE accepts_marketing accepts_marketing TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE host');
        $this->addSql('ALTER TABLE myuser CHANGE verified_email verified_email TINYINT(1) NOT NULL, CHANGE accepts_marketing accepts_marketing TINYINT(1) NOT NULL');
    }
}
