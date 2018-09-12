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

namespace oat\taoTestCenter\controller;

/**
 * @OA\Info(title="TAO Test Center API", version="0.1")
 */
class RestEligibility extends \tao_actions_RestController
{

    /**
     * @OA\Post(
     *     path="/taoTestCenter/api/eligibility",
     *     tags={"eligibility"},
     *     summary="Create new eligibility",
     *     description="Create new test center eligibility by test center uri and delivery",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="delivery",
     *                     type="string",
     *                     description="delivery URI",
     *                 ),
     *                 @OA\Property(
     *                     property="testTakers",
     *                     type="array",
     *                     description="Array of test-takers URIs",
     *                     @OA\Items(
     *                         type="string",
     *                     ),
     *                 ),
     *                 required={"delivery"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Created eligibility URI",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="success",
     *                     type="boolean",
     *                     description="`false` on failure, `true` on success",
     *                 ),
     *                 @OA\Property(
     *                     property="uri",
     *                     type="string",
     *                     description="Created eligibility URI",
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response=400, description="Invalid delivery or test-taker uri"),
     * )
     */
    public function post()
    {
    }

    /**
     * @OA\Get(
     *     path="/taoTestCenter/api/eligibility/{eligibilityUri}",
     *     tags={"eligibility"},
     *     summary="Get eligibility data",
     *     description="Get eligibility data",
     *     @OA\Parameter(
     *         name="eligibilityUri",
     *         in="path",
     *         description="Eligibility Uri",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Created eligibility URI",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Eligibility")
     *         ),
     *     ),
     *     @OA\Response(response=400, description="Invalid eligibility Uri"),
     * )
     */
    public function get()
    {
    }
}
