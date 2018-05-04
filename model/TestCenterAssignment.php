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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */
namespace oat\taoTestCenter\model;

use oat\tao\model\TaoOntology;
use oat\taoDeliveryRdf\model\GroupAssignment;
use oat\oatbox\user\User;
use oat\generis\model\OntologyAwareTrait;

/**
 * Class TestCenterAssignment
 * @access public
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @package oat\taoTestCenter\model
 */
class TestCenterAssignment extends GroupAssignment
{
    use OntologyAwareTrait;

    const PROPERTY_TESTTAKER_ASSIGNED = 'http://www.tao.lu/Ontologies/TAOTestCenter#UserAssignment';

    /**
     * @inheritdoc
     */
    public function getDeliveryIdsByUser(User $user)
    {
        $deliveryUris = array();
        foreach ($this->getTestCenterAssignments($user) as $assignment) {
            $delivery = $assignment->getOnePropertyValue($this->getProperty(EligibilityService::PROPERTY_DELIVERY_URI));
            if (!is_null($delivery) && $delivery->exists()) {
                $deliveryUris[] = $delivery->getUri();
            }
        }
        return array_unique($deliveryUris);
    }

    /**
     * Assign a user to an assignment, allowing him/her to
     * take the delivery in question
     *
     * @param array $testTakerIds
     * @param \core_kernel_classes_Resource $assignment
     */
    public function assign($testTakerIds, $assignment)
    {
        $property = $this->getProperty(self::PROPERTY_TESTTAKER_ASSIGNED);
        foreach($testTakerIds as $testTakerId) {
            $this->getResource($testTakerId)->setPropertyValue($property, $assignment);
        }
    }

    /**
     * Unassign a user from an assignment, preventing him/her to
     * take the delivery in question
     *
     * @param array $testTakerIds
     * @param \core_kernel_classes_Resource $assignment
     */
    public function unassign($testTakerIds, $assignment)
    {
        $property = $this->getProperty(self::PROPERTY_TESTTAKER_ASSIGNED);
        foreach($testTakerIds as $testTakerId) {
            $this->getResource($testTakerId)->removePropertyValue($property, $assignment);
        }
    }

    /**
     * Unassign all users from an assignment, cleanup triggered on
     * assigment deletion
     *
     * @param \core_kernel_classes_Resource $assignment
     */
    public function unassignAll($assignment)
    {
        $instances = $this->getClass(TaoOntology::SUBJECT_CLASS_URI)->searchInstances(
            [
              self::PROPERTY_TESTTAKER_ASSIGNED => $assignment->getUri()
            ],['recursive' => true, 'like' => false]
        );
        foreach($instances as $testTaker) {
            $testTaker->removePropertyValue($this->getProperty(self::PROPERTY_TESTTAKER_ASSIGNED), $assignment);
        }
    }

    /**
     * Assignments are only valid if user is also eligible
     * (non-PHPdoc)
     * @see \oat\taoDeliveryRdf\model\GroupAssignment::verifyUserAssigned()
     */
    protected function verifyUserAssigned(\core_kernel_classes_Resource $delivery, User $user)
    {
        $deliveryProp = $this->getProperty(EligibilityService::PROPERTY_DELIVERY_URI);

        //check for guest access mode
        if($this->isDeliveryGuestUser($user) && $this->hasDeliveryGuestAccess($delivery)){
            return true;
        }

        foreach ($user->getPropertyValues(self::PROPERTY_TESTTAKER_ASSIGNED) as $eligibilityId) {
            $eligibility = $this->getResource($eligibilityId);
            $eligibilityDelivery = $eligibility->getOnePropertyValue($deliveryProp);
            if ($delivery->equals($eligibilityDelivery) && $delivery->exists()) {
                $eligibilityService = $this->getServiceManager()->get(EligibilityService::SERVICE_ID);
                if ($eligibilityService->isDeliveryEligible($delivery, $user)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns all Assignments a User is assigned to (not just eligible)
     * @param User $user
     * @return \core_kernel_classes_Resource[]
     */
    protected function getTestCenterAssignments(User $user)
    {
        $assignments = [];
        foreach ($user->getPropertyValues(self::PROPERTY_TESTTAKER_ASSIGNED) as $assignmentId) {
            $assignments[] = $this->getResource($assignmentId);
        }
        return $assignments;
    }
}
