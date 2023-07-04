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

use common_exception_InconsistentData;
use core_kernel_classes_Class;
use core_kernel_classes_Container;
use core_kernel_classes_Property as Property;
use core_kernel_classes_Resource as Resource;
use core_kernel_persistence_Exception;
use Exception;
use InvalidArgumentException;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\resource\exception\DuplicateResourceException;
use oat\oatbox\event\EventManager;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\User;
use oat\tao\model\event\UserRemovedEvent;
use oat\taoDelivery\model\AssignmentService;
use oat\taoDelivery\model\execution\DeliveryExecutionContext;
use oat\taoDelivery\model\execution\DeliveryExecutionContextInterface;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoProctoring\model\monitorCache\DeliveryMonitoringData;
use oat\taoProctoring\model\monitorCache\DeliveryMonitoringService;
use oat\taoProctoring\model\ProctorService;
use oat\taoTestCenter\model\eligibility\EligiblityChanged;
use oat\taoTestCenter\model\eligibility\IneligibileException;
use oat\taoTestCenter\model\execution\TcDeliveryExecutionContext;
use oat\taoTestCenter\model\proctoring\TestCenterMonitoringService;
use oat\taoTestTaker\models\events\TestTakerRemovedEvent;

/**
 * Service to manage eligible deliveries
 */
class EligibilityService extends ConfigurableService
{
    use OntologyAwareTrait;

    public const SERVICE_ID = 'taoTestCenter/EligibilityService';

    public const CLASS_URI = 'http://www.tao.lu/Ontologies/TAOProctor.rdf#DeliveryEligibility';

    public const PROPERTY_TESTCENTER_URI = 'http://www.tao.lu/Ontologies/TAOProctor.rdf#EligibileTestCenter';

    public const PROPERTY_TESTTAKER_URI = 'http://www.tao.lu/Ontologies/TAOProctor.rdf#EligibileTestTaker';

    public const PROPERTY_DELIVERY_URI = 'http://www.tao.lu/Ontologies/TAOProctor.rdf#EligibileDelivery';

    public const PROPERTY_BYPASSPROCTOR_URI = 'http://www.tao.lu/Ontologies/TAOProctor.rdf#ByPassProctor';

    public const BOOLEAN_TRUE = 'http://www.tao.lu/Ontologies/generis.rdf#True';

    public const BOOLEAN_FALSE = 'http://www.tao.lu/Ontologies/generis.rdf#False';

    public const OPTION_MANAGEABLE = 'manageable';

    /**
     * Establishes a new eligibility
     *
     * @param Resource $testCenter
     * @param Resource $delivery
     *
     * @return bool
     *
     * @deprecated use EligibilityService::newEligibility()
     */
    public function createEligibility(Resource $testCenter, Resource $delivery)
    {
        try {
            return (bool)$this->newEligibility($testCenter, $delivery);
        } catch (DuplicateResourceException $e) {
            return false;
        }
    }

    /**
     * Establishes a new eligibility
     *
     * @param Resource $testCenter
     * @param Resource $delivery
     *
     * @return Resource
     *
     * @throws DuplicateResourceException
     * @throws common_exception_InconsistentData
     * @throws core_kernel_persistence_Exception
     */
    public function newEligibility(Resource $testCenter, Resource $delivery)
    {
        if ($this->getEligibility($testCenter, $delivery) !== null) {
            throw new DuplicateResourceException(self::CLASS_URI, []);
        }

        $eligibilty = $this->getRootClass()->createInstanceWithProperties([
            self::PROPERTY_TESTCENTER_URI => $testCenter,
            self::PROPERTY_DELIVERY_URI => $delivery,
        ]);

        //Checking if proctoring was enabled for delivery, if not - we must bypass it in established eligibility
        $proctoring = $delivery->getOnePropertyValue(new Property(ProctorService::ACCESSIBLE_PROCTOR));
        if ($proctoring instanceof Resource && $proctoring->getUri() === ProctorService::ACCESSIBLE_PROCTOR_DISABLED) {
            $this->setByPassProctor($eligibilty, true);
        }

        return $eligibilty;
    }

    /**
     * Returns the eligibility representing the link, or null if not found
     *
     * @param Resource $testCenter
     * @param Resource $delivery
     *
     * @return null|Resource eligibility resource
     * @throws common_exception_InconsistentData
     */
    public function getEligibility(Resource $testCenter, Resource $delivery)
    {
        $eligibles = $this->getRootClass()->searchInstances([
            self::PROPERTY_TESTCENTER_URI => $testCenter,
            self::PROPERTY_DELIVERY_URI => $delivery,
        ], ['recursive' => false, 'like' => false]);
        if (count($eligibles) == 0) {
            return null;
        }
        if (count($eligibles) > 1) {
            throw new common_exception_InconsistentData(
                'Multiple eligibilities for testcenter ' . $testCenter->getUri() . ' and delivery ' . $delivery->getUri(
                )
            );
        }
        return reset($eligibles);
    }

    /**
     * return the test center top level class
     *
     * @access public
     * @return core_kernel_classes_Class
     */
    public function getRootClass()
    {
        return $this->getClass(self::CLASS_URI);
    }

    /**
     * Set whether this Eligibility can by-pass the proctor authorization
     *
     * @param Resource $eligibility
     * @param bool $bypass true if the elligility can by-pass the proctor authorization
     */
    public function setByPassProctor(Resource $eligibility, $bypass = false)
    {
        $eligibility->editPropertyValues(
            new Property(self::PROPERTY_BYPASSPROCTOR_URI),
            new Resource($bypass ? self::BOOLEAN_TRUE : self::BOOLEAN_FALSE)
        );
    }

    /**
     * Get deliveries eligible at a testcenter
     *
     * @param Resource $testCenter
     * @param bool $sort
     *
     * @return \Resource[]
     */
    public function getEligibleDeliveries(Resource $testCenter, $sort = true)
    {
        $eligibles = $this->getRootClass()->searchInstances([
            self::PROPERTY_TESTCENTER_URI => $testCenter,
        ], ['recursive' => false, 'like' => false]);

        $deliveryProperty = new Property(self::PROPERTY_DELIVERY_URI);

        $deliveries = [];
        foreach ($eligibles as $eligible) {
            $delivery = $eligible->getOnePropertyValue($deliveryProperty);
            if ($delivery->exists()) {
                $deliveries[$delivery->getUri()] = $delivery;
            }
        }

        if ($sort) {
            usort($deliveries, function ($a, $b) {
                return strcmp($a->getLabel(), $b->getLabel());
            });
        }

        return $deliveries;
    }

    /**
     * Get eligibilities of a  test center
     *
     * @param Resource $testCenter
     * @param array options paginantion options
     *
     * @return array formated eligibilities
     */
    public function getEligibilities(Resource $testCenter, $options = [])
    {
        $eligibilities = [];

        $eligibles = $this->getRootClass()->searchInstances([
            self::PROPERTY_TESTCENTER_URI => $testCenter,
        ], ['recursive' => false, 'like' => false]);

        $deliveryProperty = new Property(self::PROPERTY_DELIVERY_URI);
        $byPassProperty = new Property(self::PROPERTY_BYPASSPROCTOR_URI);
        $testTakerProperty = new Property(self::PROPERTY_TESTTAKER_URI);

        foreach ($eligibles as $eligible) {
            $values = $eligible->getPropertiesValues([$deliveryProperty, $byPassProperty, $testTakerProperty]);

            $delivery = current($values[self::PROPERTY_DELIVERY_URI]);
            if ($delivery->exists()) {
                $byPass = current($values[self::PROPERTY_BYPASSPROCTOR_URI]);

                $eligibilities[] = [
                    'uri' => $eligible->getUri(),
                    'delivery' => [
                        'uri' => $delivery->getUri(),
                        'label' => $delivery->getLabel(),
                    ],
                    'byPassProctor' => $byPass instanceof Resource ? $byPass->getUri() == self::BOOLEAN_TRUE : false,
                    'testTakers' => array_map(function ($testTaker) {
                        return [
                            'uri' => $testTaker->getUri(),
                            'label' => $testTaker->getLabel(),
                        ];
                    }, $values[self::PROPERTY_TESTTAKER_URI]),
                ];
            }
        }

        if ($options['sort'] == true) {
            usort($eligibilities, function ($comparedA, $comparedB) {
                return strcmp($comparedA['delivery']['label'], $comparedB['delivery']['label']);
            });
        }

        return $eligibilities;
    }

    /**
     * Removes an eligibility by testCenter and delivery
     *
     * @param Resource $testCenter
     * @param Resource $delivery
     *
     * @return bool
     * @throws IneligibileException|common_exception_InconsistentData
     */
    public function removeEligibility(Resource $testCenter, Resource $delivery)
    {
        $eligibility = $this->getEligibility($testCenter, $delivery);
        if (is_null($eligibility)) {
            throw new IneligibileException(
                'Delivery ' . $delivery->getUri() . ' ineligible to test center ' . $testCenter->getUri()
            );
        }
        return $this->deleteEligibilityResource($eligibility);
    }

    /**
     * Removes an eligibility resource
     *
     * @param Resource $eligibility
     *
     * @return bool
     * @throws common_exception_InconsistentData
     */
    private function deleteEligibilityResource(Resource $eligibility)
    {
        $deletion = $eligibility->delete(true);
        if ($deletion) {
            $this->getAssignmentService()->unassignAll($eligibility);
        }
        return $deletion;
    }

    /**
     * @return TestCenterAssignment
     */
    public function getAssignmentService()
    {
        $assignmentService = $this->getServiceLocator()->get(AssignmentService::SERVICE_ID);
        if (!$assignmentService instanceof TestCenterAssignment) {
            throw new common_exception_InconsistentData(
                'Cannot manage testcenter assignments on alternative assignment service'
            );
        }
        return $assignmentService;
    }

    /**
     * Return ids of test-takers that are eligble in the specified context
     *
     * @param Resource $testCenter
     * @param Resource $delivery
     *
     * @return string[] identifiers of the test-takers
     */
    public function getEligibleTestTakers(Resource $testCenter, Resource $delivery)
    {
        $eligible = [];
        $eligibility = $this->getEligibility($testCenter, $delivery);
        if (!is_null($eligibility)) {
            foreach ($eligibility->getPropertyValues(new Property(self::PROPERTY_TESTTAKER_URI)) as $testTaker) {
                $eligible[] = $testTaker instanceof Resource ? $testTaker->getUri() : (string)$testTaker;
            }
        }
        return $eligible;
    }

    /**
     * Allow test-taker to be eligible for this testcenter/delivery context
     *
     * @param Resource $testCenter
     * @param Resource $delivery
     * @param Resource[] $testTakerIds
     *
     * @return bool
     * @throws IneligibileException
     * @throws common_exception_InconsistentData
     */
    public function setEligibleTestTakers(Resource $testCenter, Resource $delivery, $testTakerIds)
    {
        /** @var Resource $eligibility */
        $eligibility = $this->getEligibility($testCenter, $delivery);
        if (is_null($eligibility)) {
            throw new IneligibileException(
                'Delivery ' . $delivery->getUri() . ' ineligible to test center ' . $testCenter->getUri()
            );
        }

        $previousTestTakerCollection = $eligibility->getPropertyValues(new Property(self::PROPERTY_TESTTAKER_URI));

        $result = $eligibility->editPropertyValues(new Property(self::PROPERTY_TESTTAKER_URI), $testTakerIds);

        $eventManager = $this->getServiceLocator()->get(EventManager::CONFIG_ID);
        $eventManager->trigger(new EligiblityChanged($eligibility, $previousTestTakerCollection, $testTakerIds));

        if (!$this->isManuallyAssigned()) {
            $this->getAssignmentService()->unassign($previousTestTakerCollection, $eligibility);
            $this->getAssignmentService()->assign($testTakerIds, $eligibility);
        }

        return $result;
    }

    public function isManuallyAssigned()
    {
        return $this->hasOption(self::OPTION_MANAGEABLE) && $this->getOption(self::OPTION_MANAGEABLE) === true;
    }

    /**
     * @param Resource $delivery
     * @param User $user
     *
     * @return bool
     */
    public function isDeliveryEligible(Resource $delivery, User $user)
    {
        return null !== $this->getTestCenter($delivery, $user);
    }

    /**
     * @param Resource $delivery
     * @param User $user
     *
     * @return core_kernel_classes_Container|Resource|null
     * @throws core_kernel_persistence_Exception
     */
    public function getTestCenter(Resource $delivery, User $user)
    {
        $result = null;
        $class = new core_kernel_classes_Class(EligibilityService::CLASS_URI);
        $eligibilities = $class->searchInstances([
            EligibilityService::PROPERTY_TESTTAKER_URI => $user->getIdentifier(),
            EligibilityService::PROPERTY_DELIVERY_URI => $delivery->getUri(),
        ], ['like' => false]);

        foreach ($eligibilities as $eligibility) {
            /* @var Resource $eligibility */
            $testCenter = $eligibility->getOnePropertyValue(new Property(EligibilityService::PROPERTY_TESTCENTER_URI));
            if ($testCenter instanceof Resource && $testCenter->exists()) {
                $result = $testCenter;
                break;
            }
        }

        return $result;
    }

    /**
     * @param Resource $eligibility
     *
     * @return core_kernel_classes_Container
     * @throws core_kernel_persistence_Exception
     */
    public function getTestCenterByEligibility(Resource $eligibility)
    {
        return $eligibility->getOnePropertyValue($this->getProperty(self::PROPERTY_TESTCENTER_URI));
    }

    /**
     * Whenever or not a proctor bypass exists
     *
     * @param string $deliveryId
     * @param User $user
     *
     * @return bool
     */
    public function proctorBypassExists($deliveryId, User $user)
    {
        $bypassExists = false;
        $class = new core_kernel_classes_Class(EligibilityService::CLASS_URI);
        $eligibilities = $class->searchInstances([
            EligibilityService::PROPERTY_TESTTAKER_URI => $user->getIdentifier(),
            EligibilityService::PROPERTY_DELIVERY_URI => $deliveryId,
        ], ['like' => false]);
        foreach ($eligibilities as $eligibility) {
            if ($this->canByPassProctor($eligibility)) {
                $bypassExists = true;
                break;
            }
        }
        return $bypassExists;
    }

    /**
     * Check whether this Eligibility can by-pass the proctor authorization
     *
     * @param Resource $eligibility
     *
     * @return bool true if the elligility can by-pass the proctor authorization
     */
    public function canByPassProctor(Resource $eligibility)
    {
        $canByPass = $eligibility->getOnePropertyValue(new Property(self::PROPERTY_BYPASSPROCTOR_URI));
        return !is_null($canByPass) ? ($canByPass->getUri() == self::BOOLEAN_TRUE) : false;
    }

    public function deliveryExecutionCreated(DeliveryExecutionCreated $event)
    {
        $monitoringService = $this->getServiceLocator()->get(DeliveryMonitoringService::SERVICE_ID);
        /** @var DeliveryExecutionInterface $deliveryExecution */
        $deliveryExecution = $event->getDeliveryExecution();

        try {
            $testCenter = $this->getTestCenter($deliveryExecution->getDelivery(), $event->getUser());
            if (empty($testCenter)) {
                return;
            }

            /** @var DeliveryMonitoringData $deliverMonitoringData */
            $deliverMonitoringData = $monitoringService->getData($deliveryExecution);
            $deliverMonitoringData->update(TestCenterMonitoringService::TEST_CENTER_ID, $testCenter->getUri());

            $executionContext = $this->createExecutionContext($deliveryExecution, $testCenter);
            if ($executionContext instanceof DeliveryExecutionContextInterface) {
                $deliverMonitoringData->setDeliveryExecutionContext($executionContext);
            }

            $monitoringService->save($deliverMonitoringData);
        } catch (Exception $e) {
            $this->logWarning('Delivery monitoring data were not stored. Reason: ' . $e->getMessage(), $e->getTrace());
        }
    }

    /**
     * @param Resource $eligibility
     *
     * @return Resource
     * @throws core_kernel_persistence_Exception
     */
    public function getDelivery(Resource $eligibility)
    {
        /* @var Resource $eligibility */
        $delivery = $eligibility->getOnePropertyValue(new Property(self::PROPERTY_DELIVERY_URI));
        return $delivery;
    }

    /**
     * @param string $deliveryExecution
     * @param Resource $testCenter
     *
     * @return DeliveryExecutionContext|null
     */
    private function createExecutionContext($deliveryExecution, $testCenter)
    {
        $executionContext = null;
        try {
            $executionContext = new DeliveryExecutionContext(
                $deliveryExecution->getIdentifier(),
                $testCenter->getUri(),
                TcDeliveryExecutionContext::EXECUTION_CONTEXT_TYPE,
                $testCenter->getLabel()
            );
        } catch (InvalidArgumentException $e) {
            $this->logInfo('Delivery execution context object can not be created. Reason: ' . $e->getMessage());
        }

        return $executionContext;
    }

    public function eligiblityChange(EligiblityChanged $event)
    {
        /** @var DeliveryMonitoringService $monitoringService */
        $monitoringService = $this->getServiceLocator()->get(DeliveryMonitoringService::SERVICE_ID);

        $eligiblity = $event->getEligiblity();

        $normalize = function ($item) {
            return ($item instanceof Resource) ? $item->getUri() : $item;
        };

        $before = array_map($normalize, $event->getPreviousTestTakerCollection());
        $after = array_map($normalize, $event->getActualTestTakersCollection());

        $newTestTakers = array_diff($after, $before);

        $delivery = $this->getDelivery($eligiblity);
        $testCenter = $eligiblity->getOnePropertyValue(new Property(self::PROPERTY_TESTCENTER_URI));

        //might be we would like to remove newly uneliglbe executions later
        foreach ($newTestTakers as $testTakerUri) {
            $executions = ServiceProxy::singleton()->getUserExecutions($delivery, $testTakerUri);
            foreach ($executions as $execution) {
                $deliverMonitoringData = $monitoringService->getData($execution);
                $deliverMonitoringData->update(TestCenterMonitoringService::TEST_CENTER_ID, $testCenter->getUri());
                $monitoringService->save($deliverMonitoringData);
            }
        }
    }

    public function deletedTestTaker($event)
    {
        $userUri = null;
        if ($event instanceof TestTakerRemovedEvent) {
            $user = $event->jsonSerialize();
            $userUri = $user['testTakerUri'];
        }

        if ($event instanceof UserRemovedEvent) {
            $user = $event->jsonSerialize();
            $userUri = $user['uri'];
        }

        $eligibilities = $this->getEligibilityByTestTaker($userUri);

        foreach ($eligibilities as $eligibility) {
            $previousTestTakerCollection = $eligibility->getPropertyValues(new Property(self::PROPERTY_TESTTAKER_URI));
            $newTestTakerIds = [];
            foreach ($previousTestTakerCollection as $previousTestTaker) {
                if ($userUri !== $previousTestTaker) {
                    $newTestTakerIds[] = $previousTestTaker;
                }
            }

            $eligibility->editPropertyValues(new Property(self::PROPERTY_TESTTAKER_URI), $newTestTakerIds);

            $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);
            $eventManager->trigger(new EligiblityChanged($eligibility, $previousTestTakerCollection, $newTestTakerIds));
        }
    }

    /**
     * @param string $testTakerUri
     *
     * @return Resource[]
     */
    public function getEligibilityByTestTaker($testTakerUri)
    {
        $instances = $this->getRootClass()->searchInstances(
            [self::PROPERTY_TESTTAKER_URI => $testTakerUri], ['like' => false]
        );

        return $instances;
    }

    /**
     * @param string $deliveryUri
     *
     * @throws common_exception_InconsistentData
     */
    public function deleteEligibilitiesByDelivery($deliveryUri)
    {
        $eligibilities = $this->getRootClass()->searchInstances(
            [
                EligibilityService::PROPERTY_DELIVERY_URI => $deliveryUri,
            ], ['like' => false]
        );

        foreach ($eligibilities as $eligibility) {
            $this->deleteEligibilityResource($eligibility);
        }
    }
}
