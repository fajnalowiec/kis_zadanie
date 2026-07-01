<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701211636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ten William Shakespeare books';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "INSERT INTO book (id, title, author_id) VALUES
                (100004, 'Othello', 1),
                (100005, 'King Lear', 1),
                (100006, 'Romeo and Juliet', 1),
                (100007, 'Julius Caesar', 1),
                (100008, 'The Tempest', 1),
                (100009, 'Twelfth Night', 1),
                (100010, 'Much Ado About Nothing', 1),
                (100011, 'A Midsummer Night''s Dream', 1),
                (100012, 'The Merchant of Venice', 1),
                (100013, 'Richard III', 1)"
        );
        $this->addSql('ALTER TABLE book ALTER COLUMN id RESTART WITH 100014');
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'DELETE FROM book
            WHERE id BETWEEN 100004 AND 100013'
        );
        $this->addSql('ALTER TABLE book ALTER COLUMN id RESTART WITH 100004');
    }
}
