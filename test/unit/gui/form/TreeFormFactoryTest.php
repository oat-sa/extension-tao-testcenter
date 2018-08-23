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
namespace oat\taoTestCenter\test\unit\gui\form;

use core_kernel_classes_Resource;
use oat\taoTestCenter\model\gui\form\formFactory\FormFactoryInterface;
use oat\taoTestCenter\model\gui\form\TreeFormFactory;
use tao_helpers_form_GenerisTreeForm;

class TreeFormFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testGetForms()
    {
        $treeFormFactory = $this->getService();
        $forms = $treeFormFactory->getForms();

        $this->assertInternalType('array', $forms);
        $this->assertCount(3, $forms);
        $this->assertInstanceOf(FormFactoryInterface::class, $forms[0]);
        $this->assertInstanceOf(FormFactoryInterface::class, $forms[1]);
        $this->assertInstanceOf(FormFactoryInterface::class, $forms[2]);
    }

    /**
     * @throws \oat\oatbox\service\exception\InvalidService
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function testRenderForms()
    {
        $treeFormFactory = $this->getService();
        $renderForms = $treeFormFactory->renderForms(
            $this->getMockBuilder(core_kernel_classes_Resource::class)->disableOriginalConstructor()->getMock()
        );

        $this->assertInternalType('array', $renderForms);
        $this->assertCount(3, $renderForms);

        $this->assertInternalType('string',tao_helpers_form_GenerisTreeForm::class, $renderForms[0]);
        $this->assertInternalType('string',tao_helpers_form_GenerisTreeForm::class, $renderForms[1]);
        $this->assertInternalType('string',tao_helpers_form_GenerisTreeForm::class, $renderForms[2]);
    }

    /**
     * @return TreeFormFactory
     */
    protected function getService()
    {
        $service = $this->getMockBuilder(TreeFormFactory::class)
            ->setMethods(['buildService'])
            ->getMock();

        $service
            ->method('buildService')
            ->willReturn($this->mockFormFactory());

        $service->setOption(TreeFormFactory::OPTION_FORM_FACTORIES, [
            $this->mockFormFactory(),
            $this->mockFormFactory(),
            $this->mockFormFactory(),
        ]);

        return $service;
    }

    protected function mockFormFactory()
    {
        $form = $this->getMockForAbstractClass(FormFactoryInterface::class);
        $form
            ->method('__invoke')
            ->willReturn(
                $this->mockForm()
            );

        return $form;
    }

    protected function mockForm()
    {
        $form = $this->getMockBuilder(tao_helpers_form_GenerisTreeForm::class)
            ->setMethods(['render'])->disableOriginalConstructor()->getMock();

        $form->method('render')->willReturn('rendered form');

        return $form;
    }
}
