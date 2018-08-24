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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\taoTestCenter\test\unit\import;

use oat\tao\model\import\service\ImportServiceInterface;
use oat\taoTestCenter\model\import\TestCenterCsvImporterFactory;

class TestCenterCsvImporterFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetImporter()
    {
        /** @var TestCenterCsvImporterFactory $service */
        $service = $this->getMockBuilder(TestCenterCsvImporterFactory::class)->disableOriginalConstructor()
            ->setMethods(['buildService', 'getOption', 'propagate'])->getMock();

        $service
            ->method('buildService')
            ->willReturn($this->getMockForAbstractClass(ImportServiceInterface::class));

        $service
            ->method('propagate')
            ->will($this->returnCallback(function ($prop) {
                return $prop;
            }));

        $service->expects($this->any())
            ->method('getOption')
            ->will($this->returnCallback(function ($prop) {
                switch ($prop) {
                    case 'mappers':
                        return array(
                            'default' => array(
                                'importer' => $this->getMockForAbstractClass(ImportServiceInterface::class)
                            ),
                        );
                        break;
                    case'default-schema':
                        return array(
                            'mandatory' => array(
                                'label' => 'http://www.w3.org/2000/01/rdf-schema#label',
                            ),
                            'optional' => array()
                        );
                        break;
                }
            }));

        $this->assertInstanceOf(ImportServiceInterface::class, $service->create('default'));
    }
}
