<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240815114610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER INDEX author_follower__author_id__ind RENAME TO IDX_564623F3F675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX author_follower__follower_id__ind RENAME TO IDX_564623F3AC24F853
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_564623f3f675f31b RENAME TO author_follower__author_id__ind
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_564623f3ac24f853 RENAME TO author_follower__follower_id__ind
        SQL);
    }
}
