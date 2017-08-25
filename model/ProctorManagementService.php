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
namespace oat\taoTestCenter\model;

use oat\oatbox\user\User;
use core_kernel_classes_Resource;
use core_kernel_classes_Class;
use core_kernel_classes_Property;

/**
 * Proctor management Service
 * 
 */
class ProctorManagementService extends \tao_models_classes_GenerisService
{
    const PROPERTY_ADMINISTRATOR_URI = 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#administrator';

    const PROPERTY_AUTHORIZED_PROCTOR_URI = 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#authorizedProctor';

    const PROPERTY_ASSIGNED_PROCTOR_URI = 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#assignedProctor';

    /**
     * Allow test center administrator to authorize multiple proctors to test centers
     * @param $proctorsUri
     * @param $testCenters
     * @return bool
     */
    public function authorizeProctors($proctorsUri, $testCenters){
        $return = true;
        $property = new core_kernel_classes_Property(self::PROPERTY_AUTHORIZED_PROCTOR_URI);
        foreach($proctorsUri as $proctorUri){
            $proctor = new core_kernel_classes_Resource($proctorUri);
            $authorizedTestCenters = $proctor->getPropertyValues($property);
            $newTestCenters = array_diff($testCenters, $authorizedTestCenters);
            if(!empty($newTestCenters)){
                $propertiesValues = array(self::PROPERTY_AUTHORIZED_PROCTOR_URI => $newTestCenters);
                $return &= $proctor->setPropertiesValues($propertiesValues);
            }
        }

        return (bool) $return;
    }

    /**
     * Allow test center administrator to remove authorization to multiple proctors
     * @param $proctorsUri
     * @param $testCenters
     * @return array list of unrevokable proctors
     */
    public function unauthorizeProctors($proctorsUri, $testCenters){
        $return = true;
        $unrevokableProctors = [];
        foreach($proctorsUri as $proctorUri){
            $proctor = new core_kernel_classes_Resource($proctorUri);
            foreach($testCenters as $testCenter){
                if(!$proctor->removePropertyValue(new core_kernel_classes_Property(self::PROPERTY_AUTHORIZED_PROCTOR_URI), $testCenter)){
                    $unrevokableProctors[] = $proctor->getLabel();
                }
            }
        }

        return $unrevokableProctors;
    }

    /**
     * Allow test center administrator to assign multiple proctors to test centers
     * @param $proctorsUri
     * @param $testCenterAdminUri
     * @return bool
     */
    public function assignProctors($proctorsUri, $testCenterAdminUri){
        $return = true;
        $testCenterAdmin = new core_kernel_classes_Resource($testCenterAdminUri);
        $testCenters = $testCenterAdmin->getPropertyValues(new core_kernel_classes_Property(self::PROPERTY_ADMINISTRATOR_URI));
        if(!empty($testCenters)){
            $propertiesValues = array(self::PROPERTY_ASSIGNED_PROCTOR_URI => $testCenters);
            foreach($proctorsUri as $proctorUri){
                $proctor = new core_kernel_classes_Resource($proctorUri);
                $return &= $proctor->setPropertiesValues($propertiesValues);
            }
        }else{
            throw new \common_Exception('proctors cannot be assigned to a test center admin that has no authorized test center');
        }
        return (bool) $return;
    }

    /**
     * Return all proctor assigned to test centers managed by an admin (eventually in the list of test centers)
     * @param string $testCenterAdminUri
     * @param array $testCenters
     * @return User[]
     */
    public function getAssignedProctors($testCenterAdminUri, $testCenters = array())
    {
        $testCenterService = TestCenterService::singleton();
        $testCenterAdmin = new core_kernel_classes_Resource($testCenterAdminUri);
        $testCentersAdmin = $testCenterAdmin->getPropertyValues(new core_kernel_classes_Property(self::PROPERTY_ADMINISTRATOR_URI));

        //get all sub test centers
        foreach($testCentersAdmin as $testCenter){
            $children = $testCenterService->getSubTestCenters($testCenter);
            $testCentersAdmin = array_merge($testCentersAdmin, $children);
        }

        //get test centers in common between administrable test centers and test centers list
        if(!empty($testCenters)){
            //get parent testCenter
            $allTestCenters = $testCenters;
            foreach($testCenters as $testCenter){
                $parents = $testCenterService->getRootClass()->searchInstances(
                    array(
                        TestCenterService::PROPERTY_CHILDREN_URI => $testCenter
                    )
                    ,['recursive' => true]
                );
                $parents = array_keys($parents);
                $allTestCenters = array_merge($allTestCenters, $parents);
            }
            $testCentersAdmin = array_intersect($testCentersAdmin, $allTestCenters);
        }
        $users = array();
        foreach($testCentersAdmin as $testCenterUri){
            $assignedProctors = $this->getUserClass()->searchInstances(array(
                self::PROPERTY_ASSIGNED_PROCTOR_URI => $testCenterUri
            ), array(
                'recursive' => true,
                'like' => false
            ));
            $users = array_merge($users , $assignedProctors);
        }

        return $users;
    }

    /**
     * Return authorization for a list of test centers
     * @param $testCenters
     * @return array(proctorUri => array(testcenters))
     */
    public function getProctorsAuthorization($testCenters){
        $users = array();

        $authorizedProctors = $this->getUserClass()->searchInstances(array(
            self::PROPERTY_AUTHORIZED_PROCTOR_URI => $testCenters
        ), array(
            'recursive' => true,
            'like' => false,
        ));

        /** @var core_kernel_classes_Resource $proctor */
        foreach($authorizedProctors as $proctor){
            $testCenters = $proctor->getPropertyValues(new core_kernel_classes_Property(self::PROPERTY_AUTHORIZED_PROCTOR_URI));
            $users[$proctor->getUri()] = $testCenters;
        }

        return $users;
    }
    
    private function getUserClass()
    {
        return \tao_models_classes_UserService::singleton()->getRootClass();
    }
}
