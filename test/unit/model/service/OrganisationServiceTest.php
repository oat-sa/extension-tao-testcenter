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

use core_kernel_classes_Class;
use oat\generis\test\TestCase;
use oat\taoTestCenter\model\service\OrganisationService;
use oat\taoTestCenter\model\TestCenterService;
use PHPUnit_Framework_MockObject_MockObject;

class OrganisationServiceTest extends TestCase
{
    /**
     * @var OrganisationService
     */
    private $service;

    /**
     * @var core_kernel_classes_Class|PHPUnit_Framework_MockObject_MockObject
     */
    private $classMock;

    protected function setUp()
    {
        parent::setUp();

        $this->classMock = $this->getMockBuilder(core_kernel_classes_Class::class)
            ->disableOriginalConstructor()
            ->getMock();

        $testCenterServiceMock = $this->getMock(TestCenterService::class);
        $testCenterServiceMock->method('getRootClass')
            ->willReturn($this->classMock);

        $slMock = $this->getServiceLocatorMock([
            TestCenterService::SERVICE_ID => $testCenterServiceMock
        ]);

        $this->service = new OrganisationService();
        $this->service->setServiceLocator($slMock);
    }

    public function testGetTestCentersByOrganisationId()
    {
        $result = [0 => 'testCenter'];

        $this->classMock->expects($this->once())->method('searchInstances')
            ->with(
                ['http://www.taotesting.com/ontologies/synchro.rdf#organisationId' => 'orgId'],
                ['like' => false, 'recursive' => false]
            )->willReturn($result);

        $this->assertSame($result, $this->service->getTestCentersByOrganisationId('orgId'));
    }
}
