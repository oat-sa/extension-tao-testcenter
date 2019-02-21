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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\taoTestCenter\controller;

use common_exception_MissingParameter;
use common_exception_NotFound;
use common_exception_RestApi;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoTestCenter\model\eligibility\Eligibility;
use oat\taoTestCenter\model\EligibilityService;

class RestEligibilities extends AbstractRestController
{

    const PARAMETER_DELIVERY_ID = 'delivery';

    /**
     * @throws \common_exception_NotImplemented
     */
    public function post()
    {
        $this->returnFailure(new common_exception_RestApi('Not implemented.'));
    }

    /**
     * @OA\Get(
     *     path="/taoTestCenter/api/eligibilities",
     *     tags={"eligibilities"},
     *     summary="Search for eligibilities",
     *     description="Search for eligibilities",
     *     @OA\Parameter(
     *         name="delivery",
     *         in="query",
     *         description="Delivery Uri (Url encoded)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="testCenter",
     *         in="query",
     *         description="Test center Uri (Url encoded)",
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
     *                 example= {
     *                     "success": true,
     *                     "data": {
     *                         {
     *                             "delivery": "http://sample/first.rdf#i1536680377163170",
     *                             "testCenter": "http://sample/first.rdf#i1536680377163171",
     *                             "testTakers": {
     *                                 "http://sample/first.rdf#i1536680377163172",
     *                                 "http://sample/first.rdf#i1536680377163173"
     *                             }
     *                         }
     *                     }
     *                 }
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Eligibility not found",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={
     *                     "success": false,
     *                     "errorCode": 400,
     *                     "errorMsg": "Eligibility not found for provided search parameters.",
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
            $delivery = $this->getDeliveryFromRequest();
            $testCenter = $this->getTCFromRequest();

            /** @var EligibilityService $eligibilityService */
            $eligibilityService = $this->getServiceLocator()->get(EligibilityService::SERVICE_ID);
            $eligibility = $eligibilityService->getEligibility($testCenter, $delivery);

            if ($eligibility === null) {
                $result = [];
            } else {
                $result = [$this->propagate(new Eligibility($eligibility->getUri()))];
            }

            $this->returnJson([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            $this->returnFailure($e);
        }
    }

    /**
     * Get delivery resource from request parameters
     *
     * @return \core_kernel_classes_Resource
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
            throw new common_exception_RestApi("Delivery `{$deliveryUri}` does not exist.");
        }
    }
}
