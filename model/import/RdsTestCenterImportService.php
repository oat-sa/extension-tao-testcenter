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
use oat\tao\model\import\service\ImportMapperInterface;
use oat\taoTestCenter\model\ProctorManagementService;
use oat\taoTestCenter\model\TestCenterService;

class RdsTestCenterImportService extends AbstractImportService
{
    use OntologyAwareTrait;

    /**
     * @param ImportMapperInterface $mapper
     * @return \core_kernel_classes_Resource
     * @throws \Exception
     */
    protected function persist(ImportMapperInterface $mapper)
    {
        $properties = $mapper->getProperties();
        $class = $this->getTestCenterClass($properties);

        $results = $class->searchInstances($properties);

        if (count($results) === 0){
            $resource = $class->createInstanceWithProperties($properties);
        }else{
            $resource = current($results);
        }

        if (isset($properties[ProctorManagementService::PROPERTY_ADMINISTRATOR_URI])){
            $adminProctors = $properties[ProctorManagementService::PROPERTY_ADMINISTRATOR_URI];
            $propertiesValues = array(ProctorManagementService::PROPERTY_ADMINISTRATOR_URI => [$resource]);
            /** @var \core_kernel_classes_Resource $adminProctor */
            foreach($adminProctors as $adminProctor){
                $adminProctor->setPropertiesValues($propertiesValues);
            }
        }

        if (isset($properties[ProctorManagementService::PROPERTY_ASSIGNED_PROCTOR_URI])){
            $adminProctors = $properties[ProctorManagementService::PROPERTY_ASSIGNED_PROCTOR_URI];
            $propertiesValues = array(ProctorManagementService::PROPERTY_ASSIGNED_PROCTOR_URI => [$resource]);
            /** @var \core_kernel_classes_Resource $adminProctor */
            foreach($adminProctors as $adminProctor){
                $adminProctor->setPropertiesValues($propertiesValues);
            }
        }

        if (isset($properties[TestCenterService::PROPERTY_CHILDREN_URI])){
            $subCenters = $properties[TestCenterService::PROPERTY_CHILDREN_URI];
            $propertiesValues = array(TestCenterService::PROPERTY_CHILDREN_URI => $subCenters);
            $resource->setPropertiesValues($propertiesValues);
        }

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
}