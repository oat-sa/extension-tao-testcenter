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

namespace oat\taoTestCenter\test\unit\routing;

use oat\generis\test\TestCase;
use oat\taoTestCenter\model\routing\ApiRoute;
use oat\taoTestCenter\controller\RestEligibility;
/**
 * Class ApiRouteTest
 * @package oat\taoTestCenter\test\unit\routing
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @runInSeparateProcess
 */
class ApiRouteTest extends TestCase
{
    private $method = 'GET';

    public function setUp()
    {
        $reflectedContext = new \ReflectionClass(\Context::class);
        $reflectedContextProperty = $reflectedContext->getProperty('instance');
        $reflectedContextProperty->setAccessible(true);
        $reflectedContextProperty->setValue($this->getContextMock());
    }

    public function testResolve()
    {
        $route = new ApiRoute(new \common_ext_Extension('taoTestCenter'), 'taoTestCenter/api', []);
        $path = $route->resolve('taoTestCenter/api/eligibility');
        $this->assertEquals(RestEligibility::class . '@get', $path);

        $this->method = 'PUT';
        $path = $route->resolve('taoTestCenter/api/eligibility');
        $this->assertEquals(RestEligibility::class . '@put', $path);

        $this->method = 'POST';
        $path = $route->resolve('taoTestCenter/api/eligibility');
        $this->assertEquals(RestEligibility::class . '@post', $path);

        $this->method = 'DELETE';
        $path = $route->resolve('taoTestCenter/api/eligibility');
        $this->assertEquals(RestEligibility::class . '@delete', $path);

        $this->method = 'GET';
        $path = $route->resolve('taoTestCenter/api/foo');
        $this->assertEquals(null, $path);

        $path = $route->resolve('foo/api/eligibility');
        $this->assertEquals(null, $path);

        $path = $route->resolve('taoTestCenter/foo/eligibility');
        $this->assertEquals(null, $path);

        $this->method = 'PATCH';
        $path = $route->resolve('taoTestCenter/api/eligibility');
        $this->assertEquals(null, $path);
    }

    /**
     * @return \Context
     */
    protected function getContextMock()
    {
        $contextProphecy = $this->prophesize(\Context::class);
        $requestProphecy = $this->prophesize(\Request::class);
        $test = $this;
        $requestProphecy->getMethod()->will(function () use ($test) {
            return $test->getMethod();
        });
        $contextProphecy->getRequest()->willReturn($requestProphecy->reveal());
        return $contextProphecy->reveal();
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}
