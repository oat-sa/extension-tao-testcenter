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
 *
 */
namespace oat\taoTestCenter\model;

use tao_actions_form_Instance;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\oatbox\service\ConfigurableService;
use tao_helpers_form_FormContainer as FormContainer;

class TestCenterFormService extends ConfigurableService
{
    const SERVICE_ID = 'taoTestCenter/TestCenterFormService';

    /**
     * @param core_kernel_classes_Class $clazz
     * @param core_kernel_classes_Resource $testCenter
     * @return tao_actions_form_Instance
     */
    public function getTestCenterFormContainer(
        core_kernel_classes_Class $clazz,
        core_kernel_classes_Resource $testCenter
    ) {
        return new tao_actions_form_Instance(
            $clazz,
            $testCenter,
            [FormContainer::CSRF_PROTECTION_OPTION => true]
        );
    }
}
