<?php

namespace oat\taoTestCenter\model\service;

use core_kernel_classes_Resource;
use oat\oatbox\service\ConfigurableService;
use oat\taoSync\model\synchronizer\custom\byOrganisationId\testcenter\TestCenterByOrganisationId;
use oat\taoTestCenter\model\TestCenterService;

class OrganisationService extends ConfigurableService
{
    const SERVICE_ID = 'taoTestCenter/OrganisationService';

    /**
     * @param string $organisationId
     * @return core_kernel_classes_Resource[]
     */
    public function getTestCentersByOrganisationId($organisationId)
    {
        return $this->getTestCenterService()->getRootClass()->searchInstances(
            [TestCenterByOrganisationId::ORGANISATION_ID_PROPERTY => $organisationId],
            ['like' => false, 'recursive' => false]
        );
    }

    /**
     * @return TestCenterService
     */
    private function getTestCenterService()
    {
        return $this->getServiceLocator()->get(TestCenterService::SERVICE_ID);
    }
}
