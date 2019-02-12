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
use oat\generis\model\user\UserRdf;

/**
 * Class RestTestCenterUsers
 * @package oat\taoTestCenter\controller
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class RestTestCenterUsers extends AbstractRestController
{

    const PARAMETER_USER_URI = 'user';
    const PARAMETER_USER_ROLE = 'role';

    const AVAILABLE_ROLES_MAP = [
        'proctor' => 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#assignedProctor',
        'administrator' => 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#administrator',
    ];

    /**
     * @OA\Post(
     *     path="/taoTestCenter/api/testCenterUsers",
     *     tags={"testCenter"},
     *     summary="Assign user to the test center",
     *     description="Assign user to the test center",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="testCenter",
     *                     type="string",
     *                     description="Test center id",
     *                 ),
     *                 @OA\Property(
     *                     property="user",
     *                     type="string",
     *                     description="User id",
     *                 ),
     *                 @OA\Property(
     *                     property="role",
     *                     type="string",
     *                     description="The role to which the user should be assigned",
     *                     enum={"proctor", "administrator"},
     *                 ),
     *                 required={"testCenter, user, role"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Assign user to the test center",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="success",
     *                     type="boolean",
     *                     description="`false` on failure, `true` on success",
     *                 ),
     *                 example={
     *                     "success": true
     *                 }
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid user id or user is not allowed to be assigned to given role",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={
     *                     "success": false,
     *                     "errorCode": 0,
     *                     "errorMsg": "User does not exist",
     *                     "version": "3.3.0-sprint85"
     *                 }
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Test center not found",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={
     *                     "success": false,
     *                     "errorCode": 0,
     *                     "errorMsg": "Test center not found",
     *                     "version": "3.3.0-sprint85"
     *                 }
     *             )
     *         ),
     *     )
     * )
     */
    public function post()
    {
        $testCenter = $this->getTCFromRequest();
        $user = $this->getUserFromRequest();
        try {
            $success = $syncUser->setPropertyValue($roleProperty, $testCenter);
            $this->returnJson([
                'success' => true,
                'uri' => $success,
            ]);
        } catch (\Exception $e) {
            return $this->returnFailure($e);
        }
    }

    public function get()
    {

    }

    /**
     * @return mixed
     * @throws \common_exception_BadRequest
     * @throws \common_exception_MissingParameter
     * @throws \common_exception_NotFound
     */
    private function getUserFromRequest()
    {
        if (!$this->hasRequestParameter(self::PARAMETER_USER_URI)) {
            throw new \common_exception_MissingParameter(__('Missed %s parameter', self::PARAMETER_USER_URI));
        }
        $userUri = $this->getParameterFromRequest(self::PARAMETER_USER_URI);
        $user = $this->getAndCheckResource($userUri, TaoOntology::CLASS_URI_TAO_USER);
        $role = $this->getRoleResource();

        if (!$this->getUserService()->userHasRoles($user, $role)) {
            throw new \common_exception_BadRequest(__('User is not allowed to be assigned to given role'));
        }

        return $this->authorRoles[$user->getUri()];
    }

    protected function getRoleResource()
    {
        if (!$this->hasRequestParameter(self::PARAMETER_USER_ROLE)) {
            throw new \common_exception_MissingParameter(__('Missed %s parameter', self::PARAMETER_USER_ROLE));
        }
        $roleUri = $this->getParameterFromRequest(self::PARAMETER_USER_ROLE);
        $role = $this->getAndCheckResource($roleUri, UserRdf::PROPERTY_ROLES);
    }


    protected function getRoleProperty()
    {

    }

    /**
     * @return TestCenterService
     */
    protected function getService()
    {
        return TestCenterService::singleton();
    }

    /**
     * @return \tao_models_classes_UserService
     */
    protected function getUserService()
    {
        return $this->getServiceLocator()->get(\tao_models_classes_UserService::SERVICE_ID);
    }
}
