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
use oat\taoTestCenter\model\service\OrganisationIdLabelStrategy;
use oat\taoTestCenter\model\service\OrganisationService;
use PHPUnit_Framework_MockObject_MockObject;

class OrganisationIdLabelStrategyTest extends TestCase
{
    /**
     * @var OrganisationIdLabelStrategy
     */
    private $service;

    /**
     * @var OrganisationService|PHPUnit_Framework_MockObject_MockObject
     */
    private $organisationServiceMock;

    protected function setUp()
    {
        parent::setUp();

        $this->organisationServiceMock = $this->getMock(OrganisationService::class);

        $slMock = $this->getServiceLocatorMock([
            OrganisationService::SERVICE_ID => $this->organisationServiceMock
        ]);

        $this->service = new OrganisationIdLabelStrategy();
        $this->service->setServiceLocator($slMock);
    }

    public function testGenerateOrganisationId()
    {
        $this->organisationServiceMock->expects($this->once())
            ->method('getTestCentersByOrganisationId')
            ->with('testCenterLabel Organisation Id')
            ->willReturn([]);

        $this->assertEquals(
            'testCenterLabel Organisation Id',
            $this->service->generateOrganisationId('testCenterLabel')
        );
    }

    public function testGenerateOrganisationIdWithDuplicates()
    {
        $this->organisationServiceMock->expects($this->exactly(3))
            ->method('getTestCentersByOrganisationId')
            ->withConsecutive(
                ['testCenterLabel Organisation Id'],
                ['testCenterLabel Organisation Id 1'],
                ['testCenterLabel Organisation Id 2']
            )->willReturnOnConsecutiveCalls(
                [0 => 'testCenter'],
                [0 => 'testCenter2'],
                []
            );

        $this->assertEquals(
            'testCenterLabel Organisation Id 2',
            $this->service->generateOrganisationId('testCenterLabel')
        );
    }

    public function testGenerateOrganisationIdWithMaxDuplicates()
    {
        $this->organisationServiceMock->expects($this->exactly(101))
            ->method('getTestCentersByOrganisationId')
            ->willReturn([0 => 'testCenter']);

        $this->assertRegExp(
            '/^testCenterLabel Organisation Id .{8}$/',
            $this->service->generateOrganisationId('testCenterLabel')
        );
    }
}
