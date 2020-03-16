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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */

namespace oat\taoTestCenter\test\unit\model;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\generis\model\data\Ontology;
use oat\generis\model\resource\exception\DuplicateResourceException;
use oat\generis\test\TestCase;
use oat\taoTestCenter\model\EligibilityService;
use oat\taoTestCenter\model\TestCenterAssignment;
use oat\generis\test\MockObject;

class EligibilityServiceTest extends TestCase
{
    /**
     * @var EligibilityService
     */
    private $eligibilityService;

    /**
     * @var Ontology|MockObject
     */
    private $modelMock;

    /**
     * @var core_kernel_classes_Class|MockObject
     */
    private $deliveryEligibilityClassMock;

    /**
     * @var core_kernel_classes_Resource|MockObject
     */
    private $delivery;

    /**
     * @var core_kernel_classes_Resource|MockObject
     */
    private $testCenter;

    /**
     * @var TestCenterAssignment|MockObject
     */
    private $testCenterAssignmentMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eligibilityService = new EligibilityService([]);
        $this->delivery = $this->createMock(core_kernel_classes_Resource::class);
        $this->testCenter = $this->createMock(core_kernel_classes_Resource::class);

        // Setup ontology model mock
        $this->deliveryEligibilityClassMock = $this->createMock(core_kernel_classes_Class::class);
        $this->modelMock = $this->createMock(Ontology::class);
        $this->modelMock->method('getClass')
            ->willReturn($this->deliveryEligibilityClassMock);
        $this->eligibilityService->setModel($this->modelMock);

        // Setup service locator mock
        $this->testCenterAssignmentMock = $this->createMock(TestCenterAssignment::class);
        $slMock = $this->getServiceLocatorMock([
            TestCenterAssignment::SERVICE_ID => $this->testCenterAssignmentMock
        ]);
        $this->eligibilityService->setServiceLocator($slMock);
    }

    public function testNewEligibilityAlreadyExists()
    {
        $this->expectException(DuplicateResourceException::class);

        $this->deliveryEligibilityClassMock->expects($this->once())
            ->method('searchInstances')
            ->willReturn(['ELIGIBILITY_URI']);

        $this->eligibilityService->newEligibility($this->testCenter, $this->delivery);
    }

    public function testNewEligibilityCreated()
    {
        $expectedEligibility = $this->createMock(core_kernel_classes_Resource::class);

        $propertyMock = $this->createMock(core_kernel_classes_Resource::class);
        $propertyMock->method('getUri')
            ->willReturn('http://www.tao.lu/Ontologies/TAODelivery.rdf#ComplyEnabled');
        $this->delivery->method('getOnePropertyValue')
            ->willReturn($propertyMock);

        $this->deliveryEligibilityClassMock->expects($this->once())
            ->method('searchInstances')
            ->willReturn([]);
        $this->deliveryEligibilityClassMock->expects($this->once())
            ->method('createInstanceWithProperties')
            ->willReturn($expectedEligibility);

        $result = $this->eligibilityService->newEligibility($this->testCenter, $this->delivery);

        $this->assertSame($expectedEligibility, $result, 'Created eligibility must be as expected.');
    }

    public function testDeleteEligibilitiesByDelivery()
    {
        $eligibility1 = $this->getMockBuilder(\core_kernel_classes_Resource::class)
            ->setConstructorArgs(['uri1'])
            ->getMock();

        $eligibility2 = $this->getMockBuilder(\core_kernel_classes_Resource::class)
            ->setConstructorArgs(['uri2'])
            ->getMock();

        $eligibility3 = $this->getMockBuilder(\core_kernel_classes_Resource::class)
            ->setConstructorArgs(['uri3'])
            ->getMock();

        $this->deliveryEligibilityClassMock->expects($this->once())
            ->method('searchInstances')
            ->with(['http://www.tao.lu/Ontologies/TAOProctor.rdf#EligibileDelivery' => 'uri'], ['like' => false])
            ->willReturn([$eligibility1, $eligibility2, $eligibility3]);

        $eligibility1->expects($this->once())->method('delete')->willReturn(true);
        $eligibility2->expects($this->once())->method('delete')->willReturn(false);
        $eligibility3->expects($this->once())->method('delete')->willReturn(true);

        $this->testCenterAssignmentMock->expects($this->exactly(2))
            ->method('unassignAll')
            ->withConsecutive([$eligibility1], [$eligibility3]);

        $this->eligibilityService->deleteEligibilitiesByDelivery('uri');
    }
}

