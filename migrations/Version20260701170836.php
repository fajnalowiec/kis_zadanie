<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701170836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add initial books';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "INSERT INTO book (title, author_id) VALUES
                ('Hamlet', 1),
                ('Makbeth', 1),
                ('The Old Man and the Sea', 2),
                ('A Farewell to Arms', 2)"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM book WHERE id IN (100000, 100001, 100002, 100003)');
    }
}
