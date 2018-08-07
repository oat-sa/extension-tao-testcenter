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

namespace oat\taotestCenter\test\integration;

include_once dirname(__FILE__).'/../../../tao/includes/raw_start.php';

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyRdfs;
use oat\tao\test\TaoPhpUnitTestRunner;
use oat\taoTestCenter\model\TestCenterService;
use oat\taoTestTaker\models\TestTakerService;


/**
 * Test the Test center service
 *
 * @package taoTestCenter
 */
class TestCenterServiceTest extends TaoPhpUnitTestRunner
{

    /**
     * @var TestCenterService
     */
    protected $testCenterService = null;

    protected $subjectsService = null;

    /**
     * tests initialization
     */
    public function setUp()
    {
        \common_ext_ExtensionsManager::singleton()->getExtensionById('taoTestTaker');
        TaoPhpUnitTestRunner::initTest();
        $this->subjectsService = TestTakerService::singleton();
        $this->testCenterService = TestCenterService::singleton();
    }

    /**
     * @return \core_kernel_classes_Class|null
     */
    public function testTestCenterRoot()
    {
        $testCenterClass = $this->testCenterService->getRootClass();
        $this->assertInstanceOf('core_kernel_classes_Class', $testCenterClass);
        $this->assertEquals(TestCenterService::CLASS_URI, $testCenterClass->getUri());

        return $testCenterClass;
    }

    /**
     * @depends testTestCenterRoot
     * @param $testCenterClass
     * @return \core_kernel_classes_Class
     */
    public function testSubTestCenter($testCenterClass)
    {
        $subTestCenterLabel = 'subTestCenter class';
        $subTestCenter = $this->testCenterService->createSubClass($testCenterClass, $subTestCenterLabel);
        $this->assertInstanceOf('core_kernel_classes_Class', $subTestCenter);
        $this->assertEquals($subTestCenterLabel, $subTestCenter->getLabel());

        return $subTestCenter;
    }


    /**
     * @depends testTestCenterRoot
     * @param $testCenterClass
     * @return \core_kernel_classes_Resource
     */
    public function testTestCenterInstance($testCenterClass)
    {
        $testCenterInstanceLabel = 'test center instance';
        $testCenterInstance = $this->testCenterService->createInstance($testCenterClass, $testCenterInstanceLabel);
        $this->assertInstanceOf('core_kernel_classes_Resource', $testCenterInstance);
        $this->assertEquals($testCenterInstanceLabel, $testCenterInstance->getLabel());

        return $testCenterInstance;
    }

    /**
     * @depends testSubTestCenter
     * @param $subTestCenterClass
     * @return \core_kernel_classes_Class
     */
    public function testSubTCInstance($subTestCenterClass)
    {
        $subTCInstanceLabel = 'subTC instance';
        $subTCInstance = $this->testCenterService->createInstance($subTestCenterClass);

        $subTCInstance->removePropertyValues(new core_kernel_classes_Property(OntologyRdfs::RDFS_LABEL));
        $subTCInstance->setLabel($subTCInstanceLabel);
        $this->assertInstanceOf('core_kernel_classes_Resource', $subTCInstance);
        $this->assertEquals($subTCInstanceLabel, $subTCInstance->getLabel());

        $subTCInstanceLabel2 = 'my sub TC instance';
        $subTCInstance->setLabel($subTCInstanceLabel2);
        $this->assertEquals($subTCInstanceLabel2, $subTCInstance->getLabel());

        return $subTCInstance;
    }

    /**
     * @depends testTestCenterInstance
     * @param $testCenterInstance
     */
    public function testDeleteTCInstance($testCenterInstance)
    {
        $this->assertTrue($testCenterInstance->delete());
    }

    /**
     * @depends testSubTCInstance
     * @param $subTCInstance
     */
    public function testDeleteSubTCInstance($subTCInstance)
    {
        $this->assertTrue($subTCInstance->delete());
    }

    /**
     * @depends testSubTestCenter
     * @param $subTestCenter
     */
    public function testDeleteSubTCClass($subTestCenter)
    {
        $this->assertTrue($subTestCenter->delete());
    }
}