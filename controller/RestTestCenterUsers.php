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

/**
 * Class RestTestCenter
 * @package oat\taoTestCenter\controller
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class RestTestCenter extends AbstractRestController
{

    const PARAMETER_TEST_CENTER_CLASS = 'class';
    const PARAMETER_TEST_CENTER_LABEL = 'label';

    /**
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
     *                     property="class-uri",
     *                     type="string",
     *                     description="Class uri to import item. If not specified root class will be used.",
     *                 ),
     *                 @OA\Property(
     *                     property="class-label",
     *                     type="string",
     *                     description="Label of class to import item. If not specified root class will be used.
     * If label is not unique first match will be used.",
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
        try {
            $class = $this->getClassFromRequest($this->getService()->getRootClass());
            $resource = $class->createInstance($this->getLabelFromRequest());
            $this->returnJson([
                'success' => true,
                'uri' => $resource->getUri(),
            ]);
        } catch (\Exception $e) {
            return $this->returnFailure($e);
        }
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
     *                     "label": "Test Center ABC",
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
        try {
            $tc = $this->getTCFromRequest();
            $class = current($tc->getTypes());
            $this->returnJson([
                'label' => $tc->getLabel(),
                'class' => $class->getUri(),
            ]);
        } catch (\Exception $e) {
            return $this->returnFailure($e);
        }
    }

    /**
     * Get delivery resource from request parameters
     * @return \core_kernel_classes_Resource
     * @throws \common_exception_MissingParameter
     */
    private function getLabelFromRequest()
    {
        return $this->getParameterFromRequest(self::PARAMETER_TEST_CENTER_LABEL);
    }

    /**
     * @return TestCenterService
     */
    private function getService()
    {
        return TestCenterService::singleton();
    }
}
