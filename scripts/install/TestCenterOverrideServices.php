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

use oat\taoProctoring\model\ProctorServiceDelegator;
use oat\taoTestCenter\model\proctoring\TestCenterProctorService;
use oat\taoTestCenter\model\TestCenterAssignment;
use oat\taoDelivery\model\AssignmentService;
use oat\taoProctoring\model\ProctorService;
use oat\taoTestCenter\model\proctoring\TestCenterAuthorizationService;
use oat\taoProctoring\model\authorization\TestTakerAuthorizationService;

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
        $this->registerService(TestTakerAuthorizationService::SERVICE_ID, new TestCenterAuthorizationService());
        $this->registerProctorService();
    }

    /**
     * Add new Proctor Service to the chain responsibility
     */
    private function registerProctorService()
    {
        $proctorService = $this->getServiceManager()->get(ProctorService::SERVICE_ID);
        $config = $proctorService->getOptions();
        if (!isset($config[ProctorServiceDelegator::PROCTOR_SERVICE_HANDLERS])) {
            $config[ProctorServiceDelegator::PROCTOR_SERVICE_HANDLERS] = [];
        }

        if (!in_array(TestCenterProctorService::class, $config[ProctorServiceDelegator::PROCTOR_SERVICE_HANDLERS])) {
            $config[ProctorServiceDelegator::PROCTOR_SERVICE_HANDLERS] = array_merge([TestCenterProctorService::class],
                $config[ProctorServiceDelegator::PROCTOR_SERVICE_HANDLERS]);
        }

        $this->getServiceManager()->register(ProctorService::SERVICE_ID, new ProctorServiceDelegator($config));
    }
}
