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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-2014 (update and modification) Open Assessment Technologies SA
 */

namespace oat\taoTestCenter\scripts\update;

use oat\oatbox\event\EventManager;
use oat\tao\model\event\UserRemovedEvent;
use oat\taoProctoring\model\authorization\TestTakerAuthorizationInterface;
use oat\taoProctoring\model\ProctorServiceInterface;
use oat\taoTestCenter\model\breadcrumbs\OverriddenDeliverySelectionService;
use oat\taoTestCenter\model\breadcrumbs\OverriddenMonitorService;
use oat\taoTestCenter\model\breadcrumbs\OverriddenReportingService;
use oat\taoTestCenter\model\EligibilityService;
use oat\taoTestCenter\model\proctoring\TestCenterAuthorizationService;
use oat\taoTestCenter\model\proctoring\TestCenterProctorService;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoTestTaker\models\events\TestTakerRemovedEvent;

/**
 *
 * @access public
 * @package taoGroups
 */
class Updater extends \common_ext_ExtensionUpdater
{

    /**
     * (non-PHPdoc)
     * @see common_ext_ExtensionUpdater::update()
     */
    public function update($initialVersion)
    {
        if ($this->isBetween('0.0.1', '0.3.0')) {
            throw new \common_Exception('Upgrade unavailable');
        }

        $this->skip('0.3.0', '2.0.2');

        if ($this->isVersion('2.0.2')) {
            OntologyUpdater::syncModels();
            $this->setVersion('2.0.3');
        }

        $this->skip('2.0.3', '2.0.4');

        if ($this->isVersion('2.0.4')) {
            $delegator = $this->getServiceManager()->get(ProctorServiceInterface::SERVICE_ID);
            $delegator->registerHandler(new TestCenterProctorService());
            $this->getServiceManager()->register(ProctorServiceInterface::SERVICE_ID, $delegator);
            $this->setVersion('2.1.0');
        }

        if ($this->isVersion('2.1.0')) {
            $delegator = $this->getServiceManager()->get(TestTakerAuthorizationInterface::SERVICE_ID);
            $delegator->registerHandler(new TestCenterAuthorizationService());
            $this->getServiceManager()->register(TestTakerAuthorizationInterface::SERVICE_ID, $delegator);

            $this->setVersion('3.0.0');
        }

        $this->skip('3.0.0', '3.0.1');

        if ($this->isVersion('3.0.1')) {

            $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
            $eventManager->attach(UserRemovedEvent::EVENT_NAME, [EligibilityService::SERVICE_ID, 'deletedTestTaker']);
            $eventManager->attach(TestTakerRemovedEvent::EVENT_NAME, [EligibilityService::SERVICE_ID, 'deletedTestTaker']);
            $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

            $this->setVersion('3.1.0');
        }

        $this->skip('3.1.0', '3.1.2');

        if ($this->isVersion('3.1.2')) {
            $this->getServiceManager()->register(
                OverriddenDeliverySelectionService::SERVICE_ID,
                new OverriddenDeliverySelectionService()
            );

            $this->setVersion('3.2.0');
        }
        $this->skip('3.2.0', '3.2.3');
    }
}
