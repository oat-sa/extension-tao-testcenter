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
    const PROPERTY_GROUPS_URI = 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#contains';


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
     * delete a test center instance
     *
     * @access public
     * @param  Resource $testcenter
     * @return boolean
     */
    public function deleteTestCenter(core_kernel_classes_Resource $testcenter)
    {
        $returnValue = (bool)false;

        if (!is_null($testcenter)) {
            $returnValue = $testcenter->delete();
        }

        return (bool)$returnValue;
    }

    /**
     * Check if the Class in parameter is a subclass of the Test Center Class
     *
     * @access public
     * @param  Class clazz
     * @return boolean
     */
    public function isTestCenterClass(core_kernel_classes_Class $clazz)
    {
        return $clazz->equals($this->getRootClass()) || $clazz->isSubClassOf($this->getRootClass());
    }

    /**
     * get the test centers of a user
     *
     * @access public
     * @param  User $user
     * @return array resources of testcenter
     */
    public function getTestCenters(User $user)
    {
        $testcenters = $user->getPropertyValues(self::PROPERTY_MEMBERS_URI);
        array_walk($testcenters, function (&$testcenter) {
            $testcenter = new core_kernel_classes_Resource($testcenter);
        });

        return $testcenters;
    }

    /**
     * gets the users of a test center
     *
     * @param string $testcenterUri
     * @return array resources of users
     */
    public function getUsers($testcenterUri)
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
     *
     * @param string $testcenterUri
     * @param User $user
     * @return array resources of users
     */
    public function isProctor($testcenterUri, User $user)
    {
        $testcenters = $user->getPropertyValues(self::PROPERTY_PROCTORS_URI);
        if (!is_null($testcenters) && in_array($testcenterUri, $testcenters)) {
            return true;
        }

        return false;
    }

    public function addUser($userUri, core_kernel_classes_Resource $testcenter)
    {
        $user = new \core_kernel_classes_Resource($userUri);

        return $user->setPropertyValue(new core_kernel_classes_Property(self::PROPERTY_MEMBERS_URI), $testcenter);
    }

    public function removeUser(\core_kernel_classes_Resource $user, core_kernel_classes_Resource $testcenter)
    {
        return $user->removePropertyValue(new core_kernel_classes_Property(self::PROPERTY_MEMBERS_URI), $testcenter);
    }
}
