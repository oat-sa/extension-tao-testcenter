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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 *
 */

namespace oat\taoTestCenter\model\breadcrumbs;

use oat\taoProctoring\model\breadcrumbs\DeliverySelectionService;
use oat\taoTestCenter\model\TestCenterService;

/**
 * Provides breadcrumbs for the DeliverySelection controller.
 * @author Vilmos Kovacs <vilmos@taotesting.com>
 */
class OverriddenDeliverySelectionService extends DeliverySelectionService
{

    /**
     * Gets the breadcrumbs for the index page
     * @param string $route
     * @param array $parsedRoute
     * @return array
     */
    protected function breadcrumbsIndex($route, $parsedRoute)
    {
        $breadCrumbs = [];

        $urlContext = [];
        if (isset($parsedRoute['params'])) {
            if (isset($parsedRoute['params']['context'])) {
                $urlContext['context'] = $parsedRoute['params']['context'];
            }
        }

        // Adding the testcenter link.
        $breadCrumbs[] = [
            'id' => 'testCenterSelection',
            'url' => _url('index', 'TestCenter', 'taoTestCenter', ['link-type' => 'direct']),
            'label' => __('Test centers'),
        ];

         //Adding the current testcenter.
        if (!empty($parsedRoute['params']['context'])) {

            $testCenters = TestCenterService::singleton()->getTestCentersByProctor(\common_session_SessionManager::getSession()->getUser());
            $entries = array();
            $main = null;
            foreach ($testCenters as $testCenter) {
                $testCenterId = $testCenter->getUri();
                $crumb = [
                    'id' => $testCenterId,
                    'url' => _url('testCenter', 'TestCenter', 'taoTestCenter', ['testCenter' => $testCenter->getUri(), 'link-type' => 'direct']),
                    'label' => $testCenter->getLabel(),
                ];

                if ($testCenterId == $parsedRoute['params']['context']) {
                    $main = $crumb;
                } else {
                    $entries[] = $crumb;
                }
            }

            if ($main) {
                $main['entries'] = $entries;
            }

            $breadCrumbs[] = $main;
        }


        // Adding the original breadcrumb.
        $breadCrumbs[] = parent::breadcrumbsIndex($route, $parsedRoute);

        return $breadCrumbs;
    }
}
