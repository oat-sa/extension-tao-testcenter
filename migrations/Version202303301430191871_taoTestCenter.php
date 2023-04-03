<?php

declare(strict_types=1);

namespace oat\taoTestCenter\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoTestCenter\scripts\install\HideTaoGroups;

final class Version202303301430191871_taoTestCenter extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove taoGroups role from GlobalManager role because of collision between Groups and TestCenter extensions';
    }

    public function up(Schema $schema): void
    {
        HideTaoGroups::updateACL(false);
    }

    public function down(Schema $schema): void
    {
        HideTaoGroups::updateACL(true);
    }
}
