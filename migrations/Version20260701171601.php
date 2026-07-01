<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701171601 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add initial book loans';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "INSERT INTO book_loan (borrowed_at, returned_at, book_id, customer_id) VALUES
                ('2025-02-15', '2025-02-27', 100000, 100000),
                ('2026-06-30', NULL, 100003, 100001);"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM book_loan;');
    }
}
