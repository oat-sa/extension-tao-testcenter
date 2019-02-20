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

use common_exception_RestApi;
use common_exception_MissingParameter;
use common_exception_NotFound;
use oat\generis\model\resource\exception\DuplicateResourceException;
use oat\taoTestCenter\model\eligibility\Eligibility;
use oat\taoTestCenter\model\EligibilityService;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\tao\model\TaoOntology;

/**
 * Class RestEligibility
 * @package oat\taoTestCenter\controller
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class RestEligibility extends AbstractRestController
{

    const PARAMETER_DELIVERY_ID = 'delivery';
    const PARAMETER_ELIGIBILITY_ID = 'eligibility';
    const PARAMETER_ELIGIBILITY_PROCTORED = 'proctored';
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
     *                 @OA\Property(
     *                     property="proctored",
     *                     type="boolean",
     *                     description="Create proctored/unproctored eligibility",
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
            $proctored = $this->getProctoredFromRequest();

            /** @var EligibilityService $eligibilityService */
            $eligibilityService = $this->getServiceLocator()->get(EligibilityService::class);
            $eligibility = $eligibilityService->newEligibility($testCenter, $delivery);
            if (!$eligibility) {
                throw new \common_exception_BadRequest(__('Can\'t create eligibility. Please contact administrator.'));
            }

            if ($proctored !== null) {
                $bypass = !$proctored;
                $eligibilityService->setByPassProctor($eligibility, $bypass);
            }

            $eligibilityService->setEligibleTestTakers($testCenter, $delivery, $testTakers);
            $this->returnJson([
                'success' => true,
                'uri' => $eligibility->getUri()
            ]);
        } catch (DuplicateResourceException $e) {
            return $this->returnFailure(new common_exception_RestApi(__('Eligibility already exists')));
        } catch (\Exception $e) {
            return $this->returnFailure($e);
        }
    }

    /**
     * @throws \common_exception_NotImplemented
     * @OA\Put(
     *     path="/taoTestCenter/api/eligibility",
     *     tags={"eligibility"},
     *     summary="Update eligibility test-takers",
     *     description="Update eligibility test-takers list",
     *     @OA\Parameter(
     *         name="eligibility",
     *         in="query",
     *         description="Eligibility Uri (Url encoded)",
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
     *                 ),
     *                 @OA\Property(
     *                     property="proctored",
     *                     type="boolean",
     *                     description="Make eligibility proctored or not",
     *                 ),
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
            $proctored = $this->getProctoredFromRequest();

            /** @var EligibilityService $eligibilityService */
            $eligibilityService = $this->getServiceLocator()->get(EligibilityService::class);
            $eligibilityService->setEligibleTestTakers(
                $eligibility->getTestCenter(),
                $eligibility->getDelivery(),
                $testTakers
            );
            if ($proctored !== null) {
                $eligibilityResource = $eligibilityService->getEligibility($eligibility->getTestCenter(), $eligibility->getDelivery());
                if ($eligibilityResource instanceof \core_kernel_classes_Resource) {
                    $bypass = !$proctored;
                    $eligibilityService->setByPassProctor($eligibilityResource, $bypass);
                }
            }

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
     *     path="/taoTestCenter/api/eligibility",
     *     tags={"eligibility"},
     *     summary="Get eligibility data",
     *     description="Get eligibility data",
     *     @OA\Parameter(
     *         name="eligibility",
     *         in="query",
     *         description="Eligibility Uri (Url encoded)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Eligibility data",
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
     * Get eligibility instance from request
     * @return Eligibility
     * @throws \common_exception_MissingParameter
     * @throws common_exception_RestApi
     */
    private function getEligibilityFromRequest()
    {
        $eligibilityUri = $this->getParameterFromRequest(self::PARAMETER_ELIGIBILITY_ID);

        try {
            $resource = $this->getAndCheckResource($eligibilityUri, EligibilityService::CLASS_URI);

            return $this->propagate(new Eligibility($resource->getUri()));
        } catch (common_exception_NotFound $e) {
            throw new common_exception_RestApi(__('Eligibility `%s` does not exist.', $eligibilityUri), 404);
        }
    }

    /**
     * Get delivery resource from request parameters
     * @return \core_kernel_classes_Resource
     * @throws \common_exception_MissingParameter
     * @throws common_exception_RestApi
     */
    private function getDeliveryFromRequest()
    {
        $deliveryUri = '';
        try {
            $deliveryUri = $this->getParameterFromRequest(self::PARAMETER_DELIVERY_ID);

            return $this->getAndCheckResource($deliveryUri, DeliveryAssemblyService::CLASS_URI);
        } catch (common_exception_MissingParameter $e) {
            throw new common_exception_RestApi(__('Missed required parameter: `%s`', self::PARAMETER_DELIVERY_ID));
        } catch (common_exception_NotFound $e) {
            throw new common_exception_RestApi(__('Delivery `%s` does not exist.', $deliveryUri));
        }
    }

    /**
     * @return \core_kernel_classes_Resource[]
     * @throws common_exception_RestApi
     */
    private function getTakersFromRequest()
    {
        $result = [];
        try {
            $ids = $this->getParameterFromRequest(self::PARAMETER_TEST_TAKER_IDS);
        } catch (\common_exception_MissingParameter $e) {
            return $result;
        }

        if (is_array($ids)) {
            $result = $this->getTestTakerResources($ids);
        } else {
            throw new \common_exception_RestApi(__('`%s` parameter must be an array', self::PARAMETER_TEST_TAKER_IDS));
        }

        return $result;
    }

    /**
     * @param array $ids
     * @return \core_kernel_classes_Resource[]
     * @throws common_exception_RestApi
     */
    private function getTestTakerResources(array $ids)
    {
        $result = [];
        try {
            foreach ($ids as $testTakerUri) {
                $result[] = $this->getAndCheckResource($testTakerUri, TaoOntology::CLASS_URI_SUBJECT);
            }
        } catch (common_exception_NotFound $e) {
            throw new common_exception_RestApi(__('Test taker `%s` does not exist.', $testTakerUri));
        }

        return $result;
    }

    /**
     * Get value for proctored eligibility from request.
     *
     * @return bool|null
     */
    private function getProctoredFromRequest()
    {
        $proctored = null;
        try {
            $proctored = $this->getParameterFromRequest(self::PARAMETER_ELIGIBILITY_PROCTORED);
        } catch (common_exception_MissingParameter $e) {
            return $proctored;
        }

        return filter_var($proctored, FILTER_VALIDATE_BOOLEAN);
    }
}
