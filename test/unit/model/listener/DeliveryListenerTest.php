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
namespace oat\taoTestCenter\test\unit\model\listener;

use oat\generis\test\TestCase;
use oat\taoDeliveryRdf\model\event\DeliveryRemovedEvent;
use oat\taoTestCenter\model\EligibilityService;
use oat\taoTestCenter\model\listener\DeliveryListener;

class DeliveryListenerTest extends TestCase
{
    /**
     * @var DeliveryListener
     */
    private $deliveryListener;

    /**
     * @var EligibilityService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eligibilityServiceMock;

    protected function setUp()
    {
        parent::setUp();

        $this->eligibilityServiceMock = $this->getMock(EligibilityService::class);
        $slMock = $this->getServiceLocatorMock([
            EligibilityService::SERVICE_ID => $this->eligibilityServiceMock
        ]);

        $this->deliveryListener = new DeliveryListener();
        $this->deliveryListener->setServiceLocator($slMock);
    }

    public function testDeleteDelivery()
    {
        $deliveryUri = 'DUMMY_URI';
        $event = new DeliveryRemovedEvent($deliveryUri);

        $this->eligibilityServiceMock->expects($this->once())
            ->method('deleteEligibilitiesByDelivery')
            ->with($deliveryUri);

        $this->deliveryListener->deleteDelivery($event);
    }

    /**
     * @dataProvider dataProviderTestDeleteDeliveryWithoutValidDeliveryId
     */
    public function testDeleteDeliveryWithoutValidDeliveryId($deliveryUri)
    {
        $event = new DeliveryRemovedEvent($deliveryUri);

        $this->eligibilityServiceMock->expects($this->never())->method('deleteEligibilitiesByDelivery');

        $this->deliveryListener->deleteDelivery($event);
    }

    /**
     * @return array
     */
    public function dataProviderTestDeleteDeliveryWithoutValidDeliveryId()
    {
        return [
            'Empty delivery uri' => [
                'deliveryUri' => ''
            ],
            'Delivery uri is not a string' => [
                'deliveryUri' => 234
            ]
        ];
    }
}
