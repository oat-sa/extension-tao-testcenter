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
namespace oat\taoTestCenter\test\model;

use core_kernel_classes_Class;
use oat\generis\test\TestCase;
use oat\taoTestCenter\model\EligibilityService;
use oat\taoTestCenter\model\TestCenterAssignment;

class EligibilityServiceTest extends TestCase
{
    public function testDeleteEligibilitiesByDelivery()
    {
        $eligibilityServiceMock = $this->getMockBuilder(EligibilityService::class)
            ->setMethods(['getRootClass', 'getAssignmentService'])
            ->getMock();

        $testCenterAssignmentMock = $this->getMockBuilder(TestCenterAssignment::class)
            ->disableOriginalConstructor()
            ->setMethods(['unassignAll'])
            ->getMock();

        $classMock = $this->getMockBuilder(core_kernel_classes_Class::class)
            ->disableOriginalConstructor()
            ->setMethods(['searchInstances'])
            ->getMock();

        $eligibilityServiceMock->method('getRootClass')->willReturn($classMock);
        $eligibilityServiceMock->method('getAssignmentService')->willReturn($testCenterAssignmentMock);

        $eligibility1 = $this->getMockBuilder(\core_kernel_classes_Resource::class)
            ->setMethods(['delete'])->setConstructorArgs(['uri1'])->getMock();

        $eligibility2 = $this->getMockBuilder(\core_kernel_classes_Resource::class)
            ->setMethods(['delete'])->setConstructorArgs(['uri2'])->getMock();

        $eligibility3 = $this->getMockBuilder(\core_kernel_classes_Resource::class)
            ->setMethods(['delete'])->setConstructorArgs(['uri3'])->getMock();

        $classMock->expects($this->once())
            ->method('searchInstances')
            ->with(['http://www.tao.lu/Ontologies/TAOProctor.rdf#EligibileDelivery' => 'uri'], ['like' => false])
            ->willReturn([$eligibility1, $eligibility2, $eligibility3]);

        $eligibility1->expects($this->once())->method('delete')->willReturn(true);
        $eligibility2->expects($this->once())->method('delete')->willReturn(true);
        $eligibility3->expects($this->once())->method('delete')->willReturn(true);

        $testCenterAssignmentMock->expects($this->exactly(3))
            ->method('unassignAll')
            ->withConsecutive([$eligibility1], [$eligibility2], [$eligibility3]);

        $eligibilityServiceMock->deleteEligibilitiesByDelivery('uri');
    }
}
