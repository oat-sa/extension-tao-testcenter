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

namespace oat\taoTestCenter\model\gui\form;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoTestCenter\model\gui\form\formFactory\FormFactoryInterface;

class TreeFormFactory extends ConfigurableService
{
    use OntologyAwareTrait;

    const SERVICE_ID = 'taoTestCenter/treeFormFactory';

    const OPTION_FORM_FACTORIES = 'formFactories';

    /**
     * Generate FormFactory from configuration
     *
     * @return FormFactoryInterface[]
     * @throws \oat\oatbox\service\exception\InvalidService
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function getForms()
    {
        $formFactoryOptions = $this->hasOption(self::OPTION_FORM_FACTORIES)
            ? $this->getOption(self::OPTION_FORM_FACTORIES)
            : [];

        $formFactories = [];
        foreach ($formFactoryOptions as $formFactoryOption) {
            $formFactories[] = $this->buildService($formFactoryOption, FormFactoryInterface::class);
        }

        return $formFactories;
    }

    /**
     * Render configured forms
     *
     * @param \core_kernel_classes_Resource $testCenter
     * @return array
     * @throws \oat\oatbox\service\exception\InvalidService
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function renderForms(\core_kernel_classes_Resource $testCenter)
    {
        $forms = [];
        foreach ($this->getForms() as $form) {
            $forms[] = $form($testCenter)->render();
        }
        return $forms;
    }

}