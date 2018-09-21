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

namespace oat\taoTestCenter\model\eligibility;

use \core_kernel_classes_Resource as Resource;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use oat\taoTestCenter\model\EligibilityService;
use oat\generis\model\OntologyAwareTrait;

/**
 * Class Eligibility
 * @package oat\taoTestCenter\model\eligibility
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @OA\Schema(
 *     required={"delivery","testCenter"}
 * )
 */
class Eligibility implements \JsonSerializable, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use OntologyAwareTrait;

    /**
     * Eligibility deliveries
     * @var Resource
     * @OA\Property(
     *     description="delivery URI",
     *     type="string",
     * )
     */
    private $delivery;

    /**
     * Eligibility test-takers
     * @var Resource[]
     * @OA\Property(
     *     description="Array of test-taker URIs",
     *     @OA\Items(
     *         type="string",
     *     ),
     * )
     */
    private $testTakers = null;

    /**
     * Eligibility test-center
     * @var Resource
     * @OA\Property(
     *     description="Test center URI",
     *     type="string",
     * )
     */
    private $testCenter;

    /**
     * Eligibility identifier
     * @var string
     * @OA\Property(
     *     description="Eligibility identifier",
     *     type="string",
     * )
     */
    private $id;

    /**
     * Eligibility constructor.
     * @param string $id eligibility identifier
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return Resource
     * @throws \core_kernel_persistence_Exception
     */
    public function getDelivery()
    {
        if ($this->delivery === null) {
            $this->delivery = $this->getService()->getDelivery($this->getResource($this->id));
        }
        return $this->delivery;
    }

    /**
     * @return Resource
     * @throws \core_kernel_persistence_Exception
     */
    public function getTestCenter()
    {
        if ($this->testCenter === null) {
            $this->testCenter = $this->getService()->getTestCenterByEligibility($this->getResource($this->id));
        }
        return $this->testCenter;
    }

    /**
     * @return Resource[]
     * @throws \core_kernel_persistence_Exception
     */
    public function getTestTakers()
    {
        if ($this->testTakers === null) {
            $this->testTakers = $this->getService()->getEligibleTestTakers(
                $this->getTestCenter(),
                $this->getDelivery()
            );
        }
        return $this->testTakers;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     * @throws \core_kernel_persistence_Exception
     */
    public function jsonSerialize()
    {
        return [
            'delivery' => $this->getDelivery()->getUri(),
            'testCenter' => $this->getTestCenter()->getUri(),
            'testTakers' => $this->getTestTakers(),
            'id' => $this->getId(),
        ];
    }

    /**
     * @return EligibilityService
     */
    private function getService()
    {
        return $this->getServiceLocator()->get(EligibilityService::SERVICE_ID);
    }
}
