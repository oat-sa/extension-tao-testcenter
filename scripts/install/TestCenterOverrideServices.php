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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\taoTestCenter\scripts\install;

use oat\tao\model\user\import\UserCsvImporterFactory;
use oat\taoProctoring\model\authorization\TestTakerAuthorizationInterface;
use oat\taoProctoring\model\ProctorServiceInterface;
use oat\taoTestCenter\model\import\TestCenterAdminCsvImporter;
use oat\taoTestCenter\model\proctoring\TestCenterProctorService;
use oat\taoTestCenter\model\TestCenterAssignment;
use oat\taoDelivery\model\AssignmentService;
use oat\taoTestCenter\model\proctoring\TestCenterAuthorizationService;

/**
 * Class TestCenterOverrideServices
 * @package oat\taoTestCenter\scripts\install
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class TestCenterOverrideServices extends \common_ext_action_InstallAction
{
    /**
     * @param $params
     */
    public function __invoke($params)
    {
        $this->registerService(AssignmentService::CONFIG_ID, new TestCenterAssignment());
        $this->registerTestTakerAuthorizationService();
        $this->registerProctorService();
        $this->registerTestCenterAdminCsvImporter();
    }

    private function registerTestTakerAuthorizationService()
    {
        $delegator = $this->getServiceManager()->get(TestTakerAuthorizationInterface::SERVICE_ID);
        $delegator->registerHandler(new TestCenterAuthorizationService());
        $this->getServiceManager()->register(TestTakerAuthorizationInterface::SERVICE_ID, $delegator);
    }

    /**
     * Add new Proctor Service to the chain responsibility
     */
    private function registerProctorService()
    {
        $delegator = $this->getServiceManager()->get(ProctorServiceInterface::SERVICE_ID);
        $delegator->registerHandler(new TestCenterProctorService());
        $this->getServiceManager()->register(ProctorServiceInterface::SERVICE_ID, $delegator);
    }

    private function registerTestCenterAdminCsvImporter()
    {
        $importerFactory = $this->getServiceLocator()->get(UserCsvImporterFactory::SERVICE_ID);
        $typeOptions = $importerFactory->getOption(UserCsvImporterFactory::OPTION_MAPPERS);
        $typeOptions[TestCenterAdminCsvImporter::USER_IMPORTER_TYPE] = array(
            UserCsvImporterFactory::OPTION_MAPPERS_IMPORTER => new TestCenterAdminCsvImporter()
        );
        $importerFactory->setOption(UserCsvImporterFactory::OPTION_MAPPERS, $typeOptions);
        $this->registerService(UserCsvImporterFactory::SERVICE_ID, $importerFactory);
        return \common_report_Report::createSuccess('TestCenterAdmin csv importer successfully registered.');
    }
}
