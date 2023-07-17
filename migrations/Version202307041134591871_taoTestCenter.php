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
use oat\oatbox\service\ServiceNotFoundException;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoTestCenter\model\EligibilityService;
use oat\taoTestCenter\model\import\EligibilityCsvImporterFactory;

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
        $ontologyWithTypo = 'http://www.tao.lu/Ontologies/TAOProctor.rdf#ByPassProctor"';
        $correctOntology = EligibilityService::PROPERTY_BYPASSPROCTOR_URI;

        try {
            $this->updateConfig($ontologyWithTypo, $correctOntology);
            $this->updateDatabase($ontologyWithTypo, $correctOntology);

            $this->addReport(
                Report::createSuccess(
                    'Field name was fixed successfully for ByPassProctor field.'
                )
            );
        } catch (\Throwable $e) {
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

    /**
     * @param string $ontologyWithTypo
     * @param string $correctOntology
     *
     * @return void
     *
     * @throws ServiceNotFoundException
     * @throws \common_Exception
     * @throws \common_exception_Error
     */
    public function updateConfig(string $ontologyWithTypo, string $correctOntology): void
    {
        /** @var EligibilityCsvImporterFactory $csvFactoryService */
        $csvFactoryService = $this->getServiceLocator()->get(EligibilityCsvImporterFactory::SERVICE_ID);

        $options = $csvFactoryService->getOptions();
        if (isset($options['default-schema']['optional']['is proctored'])
         && $options['default-schema']['optional']['is proctored'] === $ontologyWithTypo
        ) {
            $options['default-schema']['optional']['is proctored'] = $correctOntology;
            $csvFactoryService->setOptions($options);
        }

        $this->getServiceLocator()->register(EligibilityCsvImporterFactory::SERVICE_ID, $csvFactoryService);
    }

    /**
     * @param string $ontologyWithTypo
     * @param string $correctOntology
     *
     * @return void
     *
     * @throws ServiceNotFoundException
     */
    public function updateDatabase(string $ontologyWithTypo, string $correctOntology): void
    {
        $persistence = $this->getServiceLocator()
            ->get(PersistenceManager::SERVICE_ID)
            ->getPersistenceById('default');

        if ($persistence instanceof \common_persistence_SqlPersistence) {
            $query = sprintf(
                "UPDATE statements SET predicate = '%s' WHERE predicate = '%s';",
                $correctOntology,
                $ontologyWithTypo
            );

            $persistence->exec($query);
        }
    }
}
