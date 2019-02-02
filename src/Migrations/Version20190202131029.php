<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190202131029 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE survey_option_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE survey_option (id INT NOT NULL, survey_id INT DEFAULT NULL, title VARCHAR(500) NOT NULL, votes INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7288C8DCB3FE509D ON survey_option (survey_id)');
        $this->addSql('ALTER TABLE survey_option ADD CONSTRAINT FK_7288C8DCB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE survey DROP options');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE survey_option_id_seq CASCADE');
        $this->addSql('DROP TABLE survey_option');
        $this->addSql('ALTER TABLE survey ADD options TEXT NOT NULL');
        $this->addSql('COMMENT ON COLUMN survey.options IS \'(DC2Type:array)\'');
    }
}
