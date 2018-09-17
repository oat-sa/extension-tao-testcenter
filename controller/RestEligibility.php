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

use oat\taoTestCenter\model\eligibility\Eligibility;
use oat\taoTestCenter\model\EligibilityService;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoTestCenter\model\TestCenterService;
use oat\tao\model\TaoOntology;

/**
 * @OA\Info(title="TAO Test Center API", version="0.1")
 */
class RestEligibility extends \tao_actions_RestController
{

    const PARAMETER_DELIVERY_ID = 'delivery';
    const PARAMETER_TEST_CENTER_ID = 'testCenter';
    const PARAMETER_TEST_TAKER_IDS = 'testTakers';

    /**
     * @throws \common_exception_NotImplemented
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
     *                     property="testCenter",
     *                     type="string",
     *                     description="test center URI",
     *                 ),
     *                 @OA\Property(
     *                     property="testTakers",
     *                     type="array",
     *                     description="Array of test-takers URIs",
     *                     @OA\Items(
     *                         type="string",
     *                     ),
     *                 ),
     *                 required={"delivery", "testCenter"}
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
     *                 example={
     *                     "success": true,
     *                     "uri": "http://sample/first.rdf#i1536680377163171"
     *                 }
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid delivery, test center or test-takers uri or eligibility already exists",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={
     *                     "success": false,
     *                     "errorCode": 0,
     *                     "errorMsg": "`testTakers` parameter must be an array",
     *                     "version": "3.3.0-sprint85"
     *                 }
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Test taker, delivery or test center not found",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={
     *                     "success": false,
     *                     "errorCode": 0,
     *                     "errorMsg": "Resource with `http://sample/first.rdf#i1536680377656966s` uri not found",
     *                     "version": "3.3.0-sprint85"
     *                 }
     *             )
     *         ),
     *     ),
     * )
     */
    public function post()
    {
        try {
            $delivery = $this->getDeliveryFromRequest();
            $testCenter = $this->getTCFromRequest();
            $testTakers = $this->getTakersFromRequest();
            $eligibilityService = $this->getServiceLocator()->get(EligibilityService::class);
            if ($eligibilityService->getEligibility($testCenter, $delivery) !== null) {
                throw new \common_exception_RestApi(__('Eligibility already exists'));
            }
            if ($eligibilityService->createEligibility($testCenter, $delivery)) {
                $eligibilityService->setEligibleTestTakers($testCenter, $delivery, $testTakers);
                $this->returnJson([
                    'success' => true,
                    'uri' => $eligibilityService->getEligibility($testCenter, $delivery)->getUri()
                ]);
            } else {
                throw new \common_exception_BadRequest(__('Can\'t create eligibility. Please contact administrator.'));
            }
        } catch (\Exception $e) {
            return $this->returnFailure($e);
        }
    }

    /**
     * @throws \common_exception_NotImplemented
     * @OA\Put(
     *     path="/taoTestCenter/api/eligibility/{eligibilityUri}",
     *     tags={"eligibility"},
     *     summary="Update eligibility test-takers",
     *     description="Update eligibility test-takers list",
     *     @OA\Parameter(
     *         name="eligibilityUri",
     *         in="path",
     *         description="Eligibility Uri",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="testTakers",
     *                     type="array",
     *                     description="Array of test-takers URIs. Remove all the test takers if not given",
     *                     @OA\Items(
     *                         type="string",
     *                     ),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Updated eligibility uri",
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
     *                     description="Updated eligibility URI",
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
     *         description="Invalid test-taker uri",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={
     *                     "success": false,
     *                     "errorCode": 0,
     *                     "errorMsg": "`testTakers` parameter must be an array",
     *                     "version": "3.3.0-sprint85"
     *                 }
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Test taker, eligibility not found",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={
     *                     "success": false,
     *                     "errorCode": 0,
     *                     "errorMsg": "Resource with `http://sample/first.rdf#i1536680377656966s` uri not found",
     *                     "version": "3.3.0-sprint85"
     *                 }
     *             )
     *         ),
     *     ),
     * )
     */
    public function put()
    {
        try {
            $eligibility = $this->getEligibilityFromRequest();
            $testTakers = $this->getTakersFromRequest();
            $eligibilityService = $this->getServiceLocator()->get(EligibilityService::class);
            $eligibilityService->setEligibleTestTakers(
                $eligibility->getTestCenter(),
                $eligibility->getDelivery(),
                $testTakers
            );
            $this->returnJson([
                'success' => true,
                'uri' => $eligibility->getId()
            ]);
        } catch (\Exception $e) {
            return $this->returnFailure($e);
        }
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
     *             @OA\Schema(
     *                 ref="#/components/schemas/Eligibility",
     *                 example={
     *                     "delivery": "http://sample/first.rdf#i1536680377163170",
     *                     "testCenter": "http://sample/first.rdf#i1536680377163171",
     *                     "testTakers": {
     *                         "http://sample/first.rdf#i1536680377163172",
     *                         "http://sample/first.rdf#i1536680377163173"
     *                     }
     *                 }
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid eligibility Uri",
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
            $this->returnJson($this->getEligibilityFromRequest());
        } catch (\Exception $e) {
            return $this->returnFailure($e);
        }
    }

    /**
     * @return Eligibility
     * @throws \common_exception_NotFound
     */
    private function getEligibilityFromRequest()
    {
        $requestParts = explode('/', $this->getRequest()->getRequestURI());
        $id = end($requestParts);
        $resource = $this->getAndCheckResource(LOCAL_NAMESPACE . '#' . $id);
        $eligibility = $this->propagate(new Eligibility($resource->getUri()));
        return $eligibility;
    }

    /**
     * Get delivery resource from request parameters
     * @return \core_kernel_classes_Resource
     * @throws \common_exception_MissingParameter
     * @throws \common_exception_NotFound
     */
    private function getDeliveryFromRequest()
    {
        $deliveryUri = $this->getParameterFromRequest(self::PARAMETER_DELIVERY_ID);
        return $this->getAndCheckResource($deliveryUri, DeliveryAssemblyService::CLASS_URI);
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
     * @return array
     * @throws \common_exception_RestApi
     * @throws \common_exception_NotFound
     */
    private function getTakersFromRequest()
    {
        $result = [];
        try {
            $ids = json_decode($this->getParameterFromRequest(self::PARAMETER_TEST_TAKER_IDS), true);
        } catch (\common_exception_MissingParameter $e) {
            return $result;
        }
        if (is_array($ids)) {
            foreach ($ids as $testTakerUri) {
                $result[] = $this->getAndCheckResource($testTakerUri, TaoOntology::CLASS_URI_SUBJECT);
            }
        } else {
            throw new \common_exception_RestApi(__('`%s` parameter must be an array', self::PARAMETER_TEST_TAKER_IDS));
        }
        return $result;
    }

    /**
     * @param $parameterName
     * @return array|bool|mixed|null|string
     * @throws \common_exception_MissingParameter
     */
    private function getParameterFromRequest($parameterName)
    {
        parse_str(file_get_contents("php://input"), $params);
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
