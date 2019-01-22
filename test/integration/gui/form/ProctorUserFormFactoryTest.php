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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoTestCenter\test\integration\gui\form;

use core_kernel_classes_Resource;
use oat\taoProctoring\model\textConverter\ProctoringTextConverter;
use oat\taoTestCenter\model\gui\form\formFactory\FormFactory;
use oat\taoTestCenter\model\gui\ProctorUserFormFactory;
use tao_helpers_form_GenerisTreeForm;

class ProctorUserFormFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $factory = $this->getFactory();

        $factory->setOption('property', 'property_string');
        $factory->setOption('title', 'title_string');
        $factory->setOption('isReversed', false);
        /** @var tao_helpers_form_GenerisTreeForm $form */
        $form = $factory->__invoke(
            $this->getMockBuilder(core_kernel_classes_Resource::class)->disableOriginalConstructor()->getMock()
        );

        $this->assertInstanceOf(tao_helpers_form_GenerisTreeForm::class, $form);
        sprintf($form->render());
    }

    /**
     * @return FormFactory
     */
    protected function getFactory()
    {
        $factory = $this->getMockBuilder(ProctorUserFormFactory::class)
            ->setMethods(['getTextConverterService', 'buildGenerisForm'])
            ->disableOriginalConstructor()->getMock();

        $factory
            ->method('getTextConverterService')
            ->willReturn(
                $this->mockProctoringTextConverter()
            );

        $factory
            ->method('buildGenerisForm')
            ->willReturn(
                $this->mockForm()
            );

        return $factory;
    }

    protected function mockForm()
    {
        $form = $this->getMockBuilder(tao_helpers_form_GenerisTreeForm::class)
            ->setMethods(['render'])->disableOriginalConstructor()->getMock();

        $form->method('render')->willReturn('rendered form');

        return $form;
    }

    protected function mockProctoringTextConverter()
    {
        return $this->getMockBuilder(ProctoringTextConverter::class)->disableOriginalConstructor()->getMock();
    }
}
