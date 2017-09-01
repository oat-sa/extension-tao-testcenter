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

use oat\oatbox\extension\InstallAction;
use oat\tao\model\event\UserRemovedEvent;
use oat\taoTestCenter\model\eligibility\EligiblityChanged;
use oat\taoTestCenter\model\EligibilityService;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoTestTaker\models\events\TestTakerRemovedEvent;

/**
 * Class RegisterTestCenterEvents
 * @package oat\taoTestCenter\scripts\install
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class RegisterTestCenterEvents extends InstallAction
{
    /**
     * @param $params
     */
    public function __invoke($params)
    {
        $this->registerEvent(EligiblityChanged::EVENT_NAME, [EligibilityService::SERVICE_ID, 'eligiblityChange']);
        $this->registerEvent(DeliveryExecutionCreated::EVENT_NAME, [EligibilityService::SERVICE_ID, 'deliveryExecutionCreated']);
        $this->registerEvent(UserRemovedEvent::EVENT_NAME, [EligibilityService::SERVICE_ID, 'deletedTestTaker']);
        $this->registerEvent(TestTakerRemovedEvent::EVENT_NAME, [EligibilityService::SERVICE_ID, 'deletedTestTaker']);
    }
}
