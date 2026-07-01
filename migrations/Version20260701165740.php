<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701165740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add William Shakespeare and Ernest Hemingway authors';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "INSERT INTO author (name, surname) VALUES
                ('William', 'Shakespeare'),
                ('Ernest', 'Hemingway')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM author WHERE id IN (1, 2)');
    }
}
