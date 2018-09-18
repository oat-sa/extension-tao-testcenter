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

use oat\taoTestCenter\model\TestCenterService;
use oat\tao\model\TaoOntology;

/**
 * @OA\Info(title="TAO Test Center API", version="0.1")
 */
class RestTestCenter extends AbstractRestController
{

    /**
     * @throws \common_exception_NotImplemented
     * @OA\Post(
     *     path="/taoTestCenter/api/testCenter",
     *     tags={"testCenter"},
     *     summary="Create new test center",
     *     description="Create new test center",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="class",
     *                     type="string",
     *                     description="class URI",
     *                 ),
     *                 @OA\Property(
     *                     property="label",
     *                     type="string",
     *                     description="Test center label",
     *                 ),
     *                 required={"label"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Created test center URI",
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
     *                     description="Created test center URI",
     *                 ),
     *                 example={
     *                     "success": true,
     *                     "uri": "http://sample/first.rdf#i1536680377163171"
     *                 }
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid class uri",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={
     *                     "success": false,
     *                     "errorCode": 0,
     *                     "errorMsg": "Class `http://sample/first.rdf#i1536680377656966` does not exists",
     *                     "version": "3.3.0-sprint85"
     *                 }
     *             )
     *         ),
     *     )
     * )
     */
    public function post()
    {
    }

    /**
     * @OA\Get(
     *     path="/taoTestCenter/api/testCenter",
     *     tags={"testCenter"},
     *     summary="Get test center data",
     *     description="Get test center data",
     *     @OA\Parameter(
     *         name="testCenter",
     *         in="query",
     *         description="testCenter Uri (Url encoded)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Test center data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="label",
     *                     type="string",
     *                     description="Test center label",
     *                 ),
     *                 @OA\Property(
     *                     property="class",
     *                     type="string",
     *                     description="Test center class URI",
     *                 ),
     *                 example={
     *                     "label": "http://sample/first.rdf#i1536680377163170",
     *                     "class": "http://sample/first.rdf#i15367360596713165"
     *                 }
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid test center Uri",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={
     *                     "success": false,
     *                     "errorCode": 0,
     *                     "errorMsg": "Resource with `http://sample/first.rdf#i15367360596713165` uri not found",
     *                     "version": "3.3.0-sprint85"
     *                 }
     *             )
     *         ),
     *     ),
     * )
     */
    public function get()
    {
    }

    /**
     * Get test center resource from request parameters
     * @return \core_kernel_classes_Resource
     * @throws \common_exception_MissingParameter
     * @throws \common_exception_NotFound
     */
    private function getTCFromRequest()
    {
        $testCenterUri = $this->getParameterFromRequest(self::PARAMETER_TEST_CENTER_ID);
        return $this->getAndCheckResource($testCenterUri, TestCenterService::CLASS_URI);
    }

    /**
     * @param $parameterName
     * @return array|bool|mixed|null|string
     * @throws \common_exception_MissingParameter
     */
    private function getParameterFromRequest($parameterName)
    {
        parse_str(file_get_contents("php://input"), $params);
        $params = array_merge($params, $this->getRequestParameters());
        if (!isset($params[$parameterName])) {
            throw new \common_exception_MissingParameter(__('Missed `%s` parameter', $parameterName));
        }
        return $params[$parameterName];
    }

    /**
     * @param $uri
     * @param null $class
     * @return \core_kernel_classes_Resource
     * @throws \common_exception_NotFound
     */
    private function getAndCheckResource($uri, $class = null)
    {
        $resource = $this->getResource($uri);
        if (!$resource->exists() || ($class !== null && !$resource->hasType($this->getClass($class)))) {
            throw new \common_exception_NotFound(__('Resource with `%s` uri not found', $uri));
        }
        return $resource;
    }
}
