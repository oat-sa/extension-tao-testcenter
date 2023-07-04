<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 *
 *
 */

declare(strict_types=1);

namespace oat\taoTestCenter\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\IrreversibleMigration;
use oat\generis\persistence\PersistenceManager;
use oat\oatbox\reporting\Report as Report;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202307041134591871_taoTestCenter extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fixes field name for ByPassProctor to a proper one (typo fix).';
    }

    public function up(Schema $schema): void
    {
        try {
            $persistence = $this->getPersistence();

            $query = sprintf(
                "UPDATE statements SET predicate = '%s' WHERE predicate = '%s';",
                \oat\taoTestCenter\model\EligibilityService::PROPERTY_BYPASSPROCTOR_URI,
                'http://www.tao.lu/Ontologies/TAOProctor.rdf#ByPassProctor"'
            );
            $persistence->exec($query);

            $this->addReport(
                Report::createSuccess(
                    'Field name was fixed successfully for ByPassProctor field.'
                )
            );
        } catch (Exception $e) {
            $this->addReport(
                Report::createError(
                    'Failed to fix field name for ByPassProctor field.',
                    [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTrace(),
                    ]
                )
            );
        }
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration(
            'This is data change migration. You need a dump or should create another migration to reverse it.'
        );
    }

    private function getPersistence()
    {
        return $this->getServiceLocator()
            ->get(PersistenceManager::SERVICE_ID)
            ->getPersistenceById('default');
    }
}
