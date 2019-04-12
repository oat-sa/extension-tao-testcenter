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

use common_exception_MissingParameter as MissingParameterException;
use common_exception_NotFound as NotFoundException;
use common_exception_RestApi as RestApiException;
use oat\taoTestCenter\model\TestCenterService;

/**
 * Class AbstractRestController
 * @package oat\taoTestCenter\controller
 * @author Aleh Hutnikau, <hutnikau@a1pt.com>
 * @OA\Info(title="TAO Test Center API", version="0.1")
 */
abstract class AbstractRestController extends \tao_actions_RestController
{

    const PARAMETER_TEST_CENTER_ID = 'testCenter';

    abstract public function post();

    abstract public function get();

    /**
     * Get test center resource from request parameters
     * @return \core_kernel_classes_Resource
     * @throws RestApiException
     * @throws NotFoundException
     */
    protected function getTCFromRequest()
    {
        $testCenterUri = '';
        try {
            $testCenterUri = $this->getParameterFromRequest(self::PARAMETER_TEST_CENTER_ID);

            return $this->getAndCheckResource($testCenterUri, TestCenterService::CLASS_URI);
        } catch (MissingParameterException $e) {
            throw new RestApiException(__('Missed required parameter: `%s`', self::PARAMETER_TEST_CENTER_ID), 400);
        } catch (NotFoundException $e) {
            throw new NotFoundException(__('Test Center `%s` does not exist.', $testCenterUri), 404);
        }
    }

    /**
     * @param $parameterName
     * @return array|bool|mixed|null|string
     * @throws MissingParameterException
     */
    protected function getParameterFromRequest($parameterName)
    {
        parse_str(file_get_contents("php://input"), $params);
        $params = array_merge($params, $this->getRequestParameters());
        if (!isset($params[$parameterName])) {
            throw new MissingParameterException(__('Missed `%s` parameter', $parameterName));
        }
        return $params[$parameterName];
    }

    /**
     * @param $uri
     * @param null $class
     * @return \core_kernel_classes_Resource
     * @throws NotFoundException
     */
    protected function getAndCheckResource($uri, $class = null)
    {
        $resource = $this->getResource($uri);
        if (!$resource->exists() || ($class !== null && !$resource->isInstanceOf($this->getClass($class)))) {
            throw new NotFoundException(__('Resource with `%s` uri not found', $uri), 404);
        }
        return $resource;
    }
}
