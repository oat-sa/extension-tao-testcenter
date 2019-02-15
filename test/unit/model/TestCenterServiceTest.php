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
namespace oat\taoTestCenter\test\unit\model;

use oat\generis\test\GenerisTestCase;
use oat\tao\model\TaoOntology;
use oat\taoTestCenter\model\TestCenterService;
use oat\oatbox\user\User;
use oat\taoProctoring\model\ProctorService;
use oat\taoTestCenter\model\ProctorManagementService;

/**
 * Class TestCenterServiceTest
 * @package oat\taoTestCenter\test\unit\model
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class TestCenterServiceTest extends GenerisTestCase
{
    /** @var \core_kernel_classes_Resource */
    protected $tc;

    /** @var \core_kernel_classes_Resource */
    protected $userResource;

    /**
     * @expectedException oat\taoTestCenter\model\exception\TestCenterException
     */
    public function testAssignUserException()
    {
        $service = $this->getService();
        $user = $this->getUserMock('proctor', $service);
        $service->assignUser($this->tc, $user, $service->getProperty(ProctorManagementService::PROPERTY_ADMINISTRATOR_URI));
    }

    public function testAssignUser()
    {
        $service = $this->getService();
        $user = $this->getUserMock('proctor', $service);
        $this->assertTrue($service->assignUser($this->tc, $user, $service->getProperty(ProctorService::ROLE_PROCTOR)));
        $assignedTc = $this->userResource->getOnePropertyValue($service->getProperty(ProctorManagementService::PROPERTY_ASSIGNED_PROCTOR_URI));
        $this->assertEquals($assignedTc->getUri(), $this->tc->getUri());
    }

    public function testUnassignUser()
    {
        $service = $this->getService();
        $user = $this->getUserMock('proctor', $service);
        $this->assertTrue($service->assignUser($this->tc, $user, $service->getProperty(ProctorService::ROLE_PROCTOR)));
        $this->assertTrue($service->unassignUser($this->tc, $user, $service->getProperty(ProctorService::ROLE_PROCTOR)));
        $this->assertFalse($service->unassignUser($this->tc, $user, $service->getProperty(ProctorService::ROLE_PROCTOR)));

        $assignedTc = $this->userResource->getOnePropertyValue($service->getProperty(ProctorManagementService::PROPERTY_ASSIGNED_PROCTOR_URI));
        $this->assertNull($assignedTc);
    }

    /**
     * @return TestCenterService
     */
    protected function getService()
    {
        $model = $this->getOntologyMock();
        $class = $model->getClass(TestCenterService::CLASS_URI);
        $this->tc = $class->createInstance('tcLabel');
        $service = new TestCenterService([]);
        $service->setModel($model);
        return $service;
    }

    /**
     * @return User
     */
    protected function getUserMock($role, $service)
    {
        $user = $this->getMockBuilder(User::class)->getMock();
        $userClass = $service->getClass(TaoOntology::CLASS_URI_TAO_USER);
        $this->userResource = $userClass->createInstance($role);
        $user->method('getIdentifier')->willReturn($this->userResource->getUri());

        if ($role === 'proctor') {
            $user->method('getRoles')->willReturn([
                ProctorService::ROLE_PROCTOR,
            ]);
        } else if ($role === 'admin') {
            $user->method('getRoles')->willReturn([
                TestCenterService::ROLE_TESTCENTER_ADMINISTRATOR
            ]);
        }

        return $user;
    }
}
