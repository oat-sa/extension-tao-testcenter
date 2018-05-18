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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\taoTestCenter\model\import;

use oat\generis\model\data\event\ResourceUpdated;
use oat\oatbox\event\EventManager;
use oat\tao\model\import\service\AbstractImportService;
use oat\tao\model\import\service\ImportMapperInterface;
use oat\taoTestCenter\model\EligibilityService;
use core_kernel_classes_Resource;

class RdsEligibilityImportService extends AbstractImportService
{
    /** @var EligibilityService */
    protected $eligibilityService;

    /**
     * @inheritdoc
     */
    protected function persist(ImportMapperInterface $mapper)
    {
        $properties = $mapper->getProperties();
        /** @var core_kernel_classes_Resource $testCenter */
        $testCenter = $properties[EligibilityService::PROPERTY_TESTCENTER_URI];
        /** @var core_kernel_classes_Resource $delivery */
        $delivery = $properties[EligibilityService::PROPERTY_DELIVERY_URI];

        $eligibilities = $this->getEligibilityService()->getRootClass()->searchInstances(array(
            EligibilityService::PROPERTY_TESTCENTER_URI => $testCenter,
            EligibilityService::PROPERTY_DELIVERY_URI => $delivery
        ));

        if (count($eligibilities) === 0) {
            $this->getEligibilityService()->createEligibility($testCenter, $delivery);
            $eligibilities = $this->getEligibilityService()->getRootClass()->searchInstances(array(
                EligibilityService::PROPERTY_TESTCENTER_URI => $testCenter,
                EligibilityService::PROPERTY_DELIVERY_URI => $delivery
            ));
        }
        $eligibility = reset($eligibilities);

        $testTakers = $properties[EligibilityService::PROPERTY_TESTTAKER_URI];
        $testTakersIds = [];
        /** @var core_kernel_classes_Resource $testTaker */
        foreach ($testTakers as $testTaker) {
            $testTakersIds[] = $testTaker->getUri();
        }

        $this->eligibilityService->setEligibleTestTakers($testCenter, $delivery, $testTakers);

        if (isset($properties[EligibilityService::PROPERTY_BYPASSPROCTOR_URI])) {
            $byPass = !boolval($properties[EligibilityService::PROPERTY_BYPASSPROCTOR_URI]);
            $this->getEligibilityService()->setByPassProctor($eligibility, $byPass);
        }

        // Trigger ResourceUpdated event for updating updatedAt field for resource
        $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);
        $eventManager->trigger(new ResourceUpdated($testCenter));

        return $eligibility;
    }

    /**
     * @return array|EligibilityService|object
     */
    protected function getEligibilityService()
    {
        if (is_null($this->eligibilityService)) {
            $this->eligibilityService = $this->getServiceLocator()->get(EligibilityService::SERVICE_ID);
        }

        return $this->eligibilityService;
    }
}