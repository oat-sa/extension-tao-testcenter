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

namespace oat\taoTestCenter\model\gui\form\formFactory;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoProctoring\model\textConverter\ProctoringTextConverter;

class FormFactory extends ConfigurableService implements FormFactoryInterface
{
    use OntologyAwareTrait;

    /** @var \core_kernel_classes_Resource */
    protected $testCenter;
    /** @var string */
    protected $title;
    /** @var \core_kernel_classes_Property */
    protected $property;
    /** @var string */
    protected $isReversed;

    /**
     * Generate a GenerisForm based on the given testCenter
     *
     * @param \core_kernel_classes_Resource $testCenter
     * @return mixed
     */
    public function __invoke(\core_kernel_classes_Resource $testCenter)
    {
        $this->testCenter = $testCenter;
        $this->validate();
        return $this->generateForm();
    }

    /**
     * Generate a GenerisForm base actual parameters
     *
     * @return \tao_helpers_form_GenerisTreeForm
     */
    protected function generateForm()
    {
        $form = $this->buildGenerisForm();
        $form->setData('title', $this->convert($this->title));
        return $form;
    }

    /**
     * @return \tao_helpers_form_GenerisTreeForm
     */
    protected function buildGenerisForm()
    {
        return $this->isReversed
            ? \tao_helpers_form_GenerisTreeForm::buildReverseTree($this->testCenter, $this->property)
            : \tao_helpers_form_GenerisTreeForm::buildTree($this->testCenter, $this->property);
    }

    /**
     * Validate arguments required to generate form
     * @return $this
     */
    protected function validate()
    {
        if (!$this->testCenter) {
            throw new \InvalidArgumentException('A test center is required for TestCenter FormFactory.');
        }

        $options = $this->getOptions();
        if (!array_key_exists('property', $options) || !is_string($options['property'])) {
            throw new \InvalidArgumentException('Option "property" is required for TestCenter FormFactory.');
        }

        if (!array_key_exists('title', $options) || !is_string($options['title'])) {
            throw new \InvalidArgumentException('Option "title" is required for TestCenter FormFactory.');
        }

        if (!array_key_exists('isReversed', $options)) {
            throw new \InvalidArgumentException('Option "isReversed" is required for TestCenter FormFactory.');
        }

        $this->property = $this->getProperty($options['property']);
        $this->title = $options['title'];
        $this->isReversed = (boolean) $options['isReversed'];

        return $this;
    }

    /**
     * Method to convert text key by textConverter value
     *
     * @param $key
     * @return string
     */
    protected function convert($key)
    {
        return $this->getTextConverterService()->get($key);
    }

    /**
     * Get the TextConverterService
     *
     * @return ProctoringTextConverter
     */
    protected function getTextConverterService()
    {
        return $this->getServiceLocator()->get(ProctoringTextConverter::SERVICE_ID);
    }

}