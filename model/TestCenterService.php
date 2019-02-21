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
use oat\tao\model\TaoOntology;
use oat\tao\model\ClassServiceTrait;
use oat\tao\model\GenerisServiceTrait;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceManager;
use oat\taoTestCenter\model\exception\TestCenterException;
use oat\taoProctoring\model\ProctorService;

/**
 * TestCenter Service for proctoring
 */
class TestCenterService extends ConfigurableService
{
    use ClassServiceTrait {
        deleteResource as protected traitDeleteResource;
    }
    use GenerisServiceTrait;

    const SERVICE_ID = 'taoTestCenter/TestCenterService';

    const CLASS_URI = 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#TestCenter';

    const PROPERTY_CHILDREN_URI = 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#children';

    /**
     * The property for all members of testcenter
     */
    const PROPERTY_MEMBER = 'http://www.w3.org/2000/01/rdf-schema#member';

    /**
     * URI of the testcenter manager, who can create, modify and delete testcenters
     * @var string
     */
    const ROLE_TESTCENTER_MANAGER = 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#TestCenterManager';

    /**
     * URI of the testcenter admin, who can create and assign Proctors to TestCenters
     * @var string
     */
    const ROLE_TESTCENTER_ADMINISTRATOR = 'http://www.tao.lu/Ontologies/TAOProctor.rdf#TestCenterAdministratorRole';

    /**
     * @deprecated
     */
    public static function singleton()
    {
        return ServiceManager::getServiceManager()->get(self::SERVICE_ID);
    }

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
     * @param core_kernel_classes_Resource $resource
     * @return bool
     * @throws \common_exception_Error
     */
    public function deleteResource(core_kernel_classes_Resource $resource)
    {
        $success = $this->traitDeleteResource($resource);
        if ($success) {
            $userClass = new \core_kernel_classes_Class(TaoOntology::CLASS_URI_TAO_USER);
            // cleanup proctors
            $users = $userClass->searchInstances(
                array(
                    ProctorManagementService::PROPERTY_ASSIGNED_PROCTOR_URI => $resource->getUri()
                ),
                array(
                    'recursive' => true,
                    'like' => false
                )
            );
            foreach ($users as $user) {
                $user->removePropertyValue(
                    new core_kernel_classes_Property(ProctorManagementService::PROPERTY_ASSIGNED_PROCTOR_URI),
                    $resource
                );
            }
            // cleanup admins
            $users = $userClass->searchInstances(
                array(
                    ProctorManagementService::PROPERTY_ADMINISTRATOR_URI => $resource->getUri()
                ),
                array(
                    'recursive' => true,
                    'like' => false
                )
            );
            foreach ($users as $user) {
                $user->removePropertyValue(
                    new core_kernel_classes_Property(ProctorManagementService::PROPERTY_ADMINISTRATOR_URI),
                    $resource
                );
            }
            $eligibilityService = $this->getServiceLocator()->get(EligibilityService::class);
            $deliveries = $eligibilityService->getEligibleDeliveries($resource);
            foreach ($deliveries as $delivery) {
                $eligibilityService->removeEligibility($resource, $delivery);
            }
        }

        return $success;
    }

    /**
     * Get test centers administered by a proctor
     *
     * @param User $user
     * @return core_kernel_classes_Resource[]
     * @throws \common_exception_Error
     */
    public function getTestCentersByProctor(User $user)
    {
        // Regular Proctoring.
        $testCenters = array_intersect(
            $user->getPropertyValues(ProctorManagementService::PROPERTY_AUTHORIZED_PROCTOR_URI),
            $user->getPropertyValues(ProctorManagementService::PROPERTY_ASSIGNED_PROCTOR_URI)
        );
        $subCenters = [];

        foreach($testCenters as $testCenter){
            $subCenters = array_merge($subCenters, $this->getSubTestCenters($testCenter));
        }

        // Administrator Proctoring.
        $adminTestCenters = $user->getPropertyValues(ProctorManagementService::PROPERTY_ADMINISTRATOR_URI);
        $adminSubCenters = [];

        foreach($adminTestCenters as $testCenter){
            $adminSubCenters = array_merge($adminSubCenters, $this->getSubTestCenters($testCenter));
        }

        $testCenters = array_merge(
            $testCenters,
            $subCenters,
            $adminTestCenters,
            $adminSubCenters
        );

        return array_map(function($uri) {
            return new core_kernel_classes_Resource($uri);
        }, $testCenters);
    }

    /**
     * Returns testcenters that are sub-testcenters of a given testcenter
     *
     * @param core_kernel_classes_Resource $testCenter
     * @return core_kernel_classes_Resource[] sub testcenters
     */
    public function getSubTestCenters($testCenter)
    {
        if(! $testCenter instanceof core_kernel_classes_Resource){
            $testCenter = new core_kernel_classes_Resource($testCenter);
        }
        $childrenProperty = new core_kernel_classes_Property(self::PROPERTY_CHILDREN_URI);
        return $testCenter->getPropertyValues($childrenProperty);

    }

    /**
     * Gets test center
     *
     * @param string $testCenterUri
     * @return core_kernel_classes_Resource
     */
    public function getTestCenter($testCenterUri)
    {
        return new core_kernel_classes_Resource($testCenterUri);
    }

    /**
     * @param core_kernel_classes_Resource $testCenter
     * @param User $user
     * @param core_kernel_classes_Resource $role
     * @return bool
     * @throws TestCenterException
     */
    public function assignUser(\core_kernel_classes_Resource $testCenter, User $user, \core_kernel_classes_Resource $role)
    {
        $userResource = $this->getResource($user->getIdentifier());
        $userRoles = $user->getRoles();

        if (!in_array($role->getUri(), $userRoles)) {
            throw new TestCenterException(__('User with given role cannot be assigned to the test center.'));
        }

        $property = $this->getAssigneeProperty($role);
        //remove value to avoid duplication
        $userResource->removePropertyValue($property, $testCenter);
        return $userResource->setPropertyValue($property, $testCenter);
    }

    /**
     * @param core_kernel_classes_Resource $testCenter
     * @param User $user
     * @param core_kernel_classes_Resource $role
     * @return bool
     * @throws TestCenterException
     */
    public function unassignUser(\core_kernel_classes_Resource $testCenter, User $user, \core_kernel_classes_Resource $role)
    {
        $userResource = $this->getResource($user->getIdentifier());
        return $userResource->removePropertyValue($this->getAssigneeProperty($role), $testCenter);
    }

    /**
     * @param core_kernel_classes_Resource $role
     * @return core_kernel_classes_Property
     * @throws TestCenterException
     */
    protected function getAssigneeProperty(\core_kernel_classes_Resource $role)
    {
        switch ($role->getUri()) {
            case self::ROLE_TESTCENTER_ADMINISTRATOR:
                $prop = $this->getProperty(ProctorManagementService::PROPERTY_ADMINISTRATOR_URI);
                break;
            case ProctorService::ROLE_PROCTOR:
                $prop = $this->getProperty(ProctorManagementService::PROPERTY_ASSIGNED_PROCTOR_URI);
                break;
            default:
                throw new TestCenterException(__('Role is not allowed.'));
        }
        return $prop;
    }

}
