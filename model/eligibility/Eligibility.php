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

/**
 * Class Eligibility
 * @package oat\taoTestCenter\model\eligibility
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @OA\Schema(
 *     required={"delivery","testCenter"}
 * )
 */
class Eligibility implements \JsonSerializable
{
    /**
     * Eligibility deliveries
     * @var string
     * @OA\Property(
     *     description="delivery URI",
     * )
     */
    private $delivery;

    /**
     * Eligibility test-takers
     * @var array
     * @OA\Property(
     *     description="Array of test-taker URIs",
     *     @OA\Items(
     *         type="string",
     *     ),
     * )
     */
    private $testTakers = [];

    /**
     * Eligibility test-center
     * @var string
     * @OA\Property(
     *     description="Test center URI",
     * )
     */
    private $testCenter;

    /**
     * Eligibility constructor.
     * @param string $delivery delivery Uri
     * @param string $testCenter delivery Uri
     * @param array $testTakers
     */
    public function __construct($delivery, $testCenter, array $testTakers = [])
    {
        $this->delivery = $delivery;
        $this->testCenter = $testCenter;
        $this->testTakers = $testTakers;
    }

    /**
     * @return string
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * @return string
     */
    public function getTestCenter()
    {
        return $this->testCenter;
    }

    /**
     * @return array
     */
    public function getTestTakers()
    {
        return $this->testTakers;
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return json_encode([
            'delivery' => $this->getDelivery(),
            'testCenter' => $this->getTestCenter(),
            'testTakers' => $this->getTestTakers(),
        ]);
    }
}
