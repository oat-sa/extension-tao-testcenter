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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */
namespace oat\taoTestCenter\test\model\listener;

use oat\generis\test\TestCase;
use oat\taoDeliveryRdf\model\event\DeliveryRemovedEvent;
use oat\taoDeliveryRdf\model\event\DeliveryUpdatedEvent;
use oat\taoTestCenter\model\EligibilityService;
use oat\taoTestCenter\model\listener\DeliveryListener;

class DeliveryListenerTest extends TestCase
{
    public function testDeleteDelivery()
    {
        $eligibilityServiceMock = $this->getMockBuilder(EligibilityService::class)
            ->setMethods(['deleteEligibilitiesByDelivery'])
            ->getMock();

        $serviceLocatorMock = $this->getServiceLocatorMock([EligibilityService::SERVICE_ID => $eligibilityServiceMock]);

        $serviceMock = $this->getMockBuilder(DeliveryListener::class)
            ->setMethods(['getServiceLocator'])
            ->getMock();

        $eventMock = $this->getMockBuilder(DeliveryRemovedEvent::class)
            ->setMethods(['jsonSerialize'])
            ->disableOriginalConstructor()
            ->getMock();

        $eventMock->expects($this->once())
            ->method('jsonSerialize')
            ->willReturn(['delivery' => 'deliveryUrl']);

        $serviceMock->method('getServiceLocator')->willReturn($serviceLocatorMock);

        $eligibilityServiceMock->expects($this->once())
            ->method('deleteEligibilitiesByDelivery')
            ->with('deliveryUrl');

        $serviceMock->deleteDelivery($eventMock);
    }

    public function testDeleteDeliveryWithWrongEvent()
    {
        $eligibilityServiceMock = $this->getMockBuilder(EligibilityService::class)
            ->setMethods(['deleteEligibilitiesByDelivery'])
            ->getMock();

        $serviceLocatorMock = $this->getServiceLocatorMock([EligibilityService::SERVICE_ID => $eligibilityServiceMock]);

        $serviceMock = $this->getMockBuilder(DeliveryListener::class)
            ->setMethods(['getServiceLocator'])
            ->getMock();

        $eventMock = $this->getMockBuilder(DeliveryRemovedEvent::class)
            ->setMethods(['jsonSerialize'])
            ->disableOriginalConstructor()
            ->getMock();

        $eventMock->expects($this->once())
            ->method('jsonSerialize')
            ->willReturn(['invalidData' => 'deliveryUrl']);

        $serviceMock->method('getServiceLocator')->willReturn($serviceLocatorMock);

        $eligibilityServiceMock->expects($this->never())->method('deleteEligibilitiesByDelivery');

        $serviceMock->deleteDelivery($eventMock);

        $eventMock2 = $this->getMockBuilder(DeliveryUpdatedEvent::class)
            ->setMethods(['jsonSerialize'])
            ->disableOriginalConstructor()
            ->getMock();

        $eventMock2->expects($this->never())->method('jsonSerialize');

        $serviceMock->method('getServiceLocator')->willReturn($serviceLocatorMock);

        $eligibilityServiceMock->expects($this->never())->method('deleteEligibilitiesByDelivery');

        $serviceMock->deleteDelivery($eventMock2);
    }
}
