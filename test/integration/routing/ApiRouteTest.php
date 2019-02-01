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

namespace oat\taoTestCenter\test\integration\routing;

use common_ext_Extension;
use oat\generis\test\TestCase;
use oat\taoTestCenter\model\routing\ApiRoute;
use oat\taoTestCenter\controller\RestEligibility;
use GuzzleHttp\Psr7\ServerRequest;

/**
 * Class ApiRouteTest
 * @package oat\taoTestCenter\test\unit\routing
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class ApiRouteTest extends TestCase
{
    public function testResolve()
    {
        $route = new ApiRoute(new common_ext_Extension('taoTestCenter'), 'taoTestCenter/api', []);
        $path = $route->resolve(new ServerRequest('GET', '/taoTestCenter/api/eligibility'));
        $this->assertEquals(RestEligibility::class . '@get', $path);

        $path = $route->resolve(new ServerRequest('PUT', '/taoTestCenter/api/eligibility'));
        $this->assertEquals(RestEligibility::class . '@put', $path);

        $path = $route->resolve(new ServerRequest('POST', '/taoTestCenter/api/eligibility'));
        $this->assertEquals(RestEligibility::class . '@post', $path);

        $path = $route->resolve(new ServerRequest('DELETE', '/taoTestCenter/api/eligibility'));
        $this->assertEquals(RestEligibility::class . '@delete', $path);

        $path = $route->resolve(new ServerRequest('GET', '/taoTestCenter/api/foo'));
        $this->assertEquals(null, $path);

        $path = $route->resolve(new ServerRequest('GET', '/foo/api/eligibility'));
        $this->assertEquals(null, $path);

        $path = $route->resolve(new ServerRequest('GET', '/taoTestCenter/foo/eligibility'));
        $this->assertEquals(null, $path);

        $path = $route->resolve(new ServerRequest('PATCH', '/taoTestCenter/api/eligibility'));
        $this->assertEquals(null, $path);
    }
}
