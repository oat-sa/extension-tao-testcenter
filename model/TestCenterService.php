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

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use oat\oatbox\user\User;
use oat\taoTestTaker\models\TestTakerService;

/**
 * Service methods to manage the test center business models using the RDF API.
 *
 * @access public
 * @package taoTestCenter
 */
class TestCenterService
    extends \tao_models_classes_ClassService
{
    const CLASS_URI = 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#TestCenter';
    
    const PROPERTY_MEMBERS_URI = 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#member';
    
    const PROPERTY_PROCTORS_URI = 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#proctor';
    
    const PROPERTY_DELIVERY_URI = 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#administers';


    /**
     * return the test center top level class
     *
     * @access public
     * @return core_kernel_classes_Class
     */
    public function getRootClass()
    {
        return new core_kernel_classes_Class(self::CLASS_URI);
    }
    
    /**
     * Get test centers administered by a proctor
     * 
     * @param User $user
     * @return core_kernel_classes_Resource[]
     */
    public function getTestCentersByProctor(User $user)
    {
        $testcenters = array();
        foreach ($user->getPropertyValues(self::PROPERTY_PROCTORS_URI) as $id) {
            $testcenters[] = new core_kernel_classes_Resource($id);
        }
        return $testcenters;
    }
    

    /**
     * Get test centers a test-taker is assigned to
     *
     * @access public
     * @param  User $user
     * @return array resources of testcenter
     */
    public function getTestCentersByTestTaker(User $user)
    {
        $testcenters = $user->getPropertyValues(self::PROPERTY_MEMBERS_URI);
        array_walk($testcenters, function (&$testcenter) {
            $testcenter = new core_kernel_classes_Resource($testcenter);
        });

        return $testcenters;
    }
    
    /**
     * Get test centers a delivery can be taken from
     *
     * @access public
     * @param  string $deliveryId
     * @return array resources of testcenter
     */
    public function getTestCentersByDelivery($deliveryId)
    {
        return $this->getRootClass()->searchInstances(array(
            self::PROPERTY_DELIVERY_URI => $deliveryId
        ), array(
            'recursive' => true, 'like' => false
        ));
    }
    
    /**
     *
     * @param User $user
     * @return \taoDelivery_models_classes_DeliveryRdf[]
     */
    public function getDeliveries($testcenterUri)
    {
        $testcenter = new core_kernel_classes_Resource($testcenterUri);
        $deliveryProp = new core_kernel_classes_Property(self::PROPERTY_DELIVERY_URI);
        
        $deliveries = array();
        foreach ($testcenter->getPropertyValues($deliveryProp) as $delResource) {
            $deliveries[] = new \taoDelivery_models_classes_DeliveryRdf($delResource);
        }
        return $deliveries;
    }

    /**
     * gets the users of a test center
     *
     * @param string $testcenterUri
     * @return array resources of users
     */
    public function getTestTakers($testcenterUri)
    {
        $userClass = TestTakerService::singleton()->getRootClass();
        $users = $userClass->searchInstances(array(
            self::PROPERTY_MEMBERS_URI => $testcenterUri
        ), array(
            'recursive' => true,
            'like' => false
        ));

        return $users;
    }
    
    /**
     * Add a test-taker to a test center
     * 
     * @param string $userUri
     * @param core_kernel_classes_Resource $testcenter
     * @return boolean
     */
    public function addTestTaker($userUri, core_kernel_classes_Resource $testcenter)
    {
        $user = new \core_kernel_classes_Resource($userUri);
        return $user->setPropertyValue(new core_kernel_classes_Property(self::PROPERTY_MEMBERS_URI), $testcenter);
    }
    
    /**
     * Remove a test-taker from a test center
     * 
     * @param string $userUri
     * @param core_kernel_classes_Resource $testcenter
     * @return boolean
     */
    public function removeTestTaker($userUri, core_kernel_classes_Resource $testcenter)
    {
        $user = new \core_kernel_classes_Resource($userUri);
        return $user->removePropertyValue(new core_kernel_classes_Property(self::PROPERTY_MEMBERS_URI), $testcenter);
    }
}
