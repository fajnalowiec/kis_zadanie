<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701164941 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add John Doe and Mary Smith customers';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "INSERT INTO customer (name, surname) VALUES
                ('John', 'Doe'),
                ('Mary', 'Smith')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM customer WHERE id IN (100000, 100001)');
    }
}
