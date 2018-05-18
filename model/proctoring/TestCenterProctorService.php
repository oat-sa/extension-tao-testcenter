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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 *
 */
namespace oat\taoTestCenter\model\proctoring;

use oat\generis\model\kernel\users\UserInternalInterface;
use oat\oatbox\user\User;
use oat\taoProctoring\model\monitorCache\DeliveryMonitoringService;
use oat\taoProctoring\model\ProctorService;
use oat\taoTestCenter\model\EligibilityService;

/**
 * Sample Delivery Service for proctoring
 *
 * @author Joel Bout <joel@taotesting.com>
 */
class TestCenterProctorService extends ProctorService
{
    /**
     * Gets all deliveries available for a proctor
     * @param User $proctor
     * @return array
     */
    public function getProctorableDeliveries(User $proctor, $context = null)
    {
        if (empty($context)) {
            throw new \common_exception_Error('No testcenter specified in '.__FUNCTION__);
        }
        $testCenter = $this->getResource($context);
        $elibilityService = $this->getServiceManager()->get(EligibilityService::SERVICE_ID);
        return $elibilityService->getEligibleDeliveries($testCenter, false);
    }
    
    /**
     * @param null $delivery
     * @param null $context
     * @param array $options
     * @return array
     */
    protected function getCriteria($delivery = null, $context = null, $options = [])
    {
        if (empty($context)) {
            throw new \common_Exception('No testcenter specified in '.__FUNCTION__);
        }
        $criteria = [
            [TestCenterMonitoringService::TEST_CENTER_ID => $context]
        ];
        if ($delivery !== null) {
            $criteria[] = [DeliveryMonitoringService::DELIVERY_ID => $delivery->getUri()];
        }
        if (!empty($options['filters'])) {
            $criteria[] = $options['filters'];
        }

        return $criteria;
    }

    public function isSuitable(User $user, $deliveryId = null)
    {
        return in_array(ProctorService::ROLE_PROCTOR, $user->getRoles())
            && is_a($user, UserInternalInterface::class);
    }
}
