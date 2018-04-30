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

namespace oat\taoTestCenter\model\import;

use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdf;
use oat\tao\model\import\service\AbstractImportService;
use oat\tao\model\import\service\ImportMapper;
use oat\taoTestCenter\model\TestCenterService;

class RdsTestCenterImportService extends AbstractImportService implements TestCenterImportServiceInterface
{
    use OntologyAwareTrait;

    /**
     * @param array $data
     * @param array $extraProperties
     * @return array
     */
    protected function formatData(array $data, array $extraProperties)
    {
        return $data;
    }

    /**
     * @param ImportMapper $mapper
     * @return \core_kernel_classes_Resource
     * @throws \Exception
     */
    protected function persist(ImportMapper $mapper)
    {
        if (!$mapper instanceof TestCenterMapper) {
            throw new \Exception('Mapper should be a TestCenterMapper');
        }

        $properties = $mapper->getProperties();
        $class = $this->getTestCenterClass($properties);
        $resource = $class->createInstanceWithProperties($properties);

        return $resource;
    }

    /**
     * @param $properties
     * @return \core_kernel_classes_Class
     */
    protected function getTestCenterClass($properties)
    {
        $class = $this->getClass(TestCenterService::CLASS_URI);
        if (isset($properties[OntologyRdf::RDF_TYPE])){
            $class = $this->getClass($properties[OntologyRdf::RDF_TYPE]);
            if ($class->isSubClassOf($class)) {
                return $class;
            }
        }
        return $class;

    }
    /**
     * @param array $data
     * @param array$csvControls
     * @param string $delimiter
     */
    protected function applyCsvImportRules(array $data, array $csvControls, $delimiter)
    {
    }
}