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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\taoTestCenter\helper;

use \core_kernel_classes_Resource;
use oat\taoTestCenter\helper\BreadcrumbsHelper;

/**
 * Allow creating breakcrumbs easily
 */
class TestCenterBreadcrumbs extends BreadcrumbsHelper
{

    /**
     * Create breadcrumb for TestCenter::index
     * @return array
     */
    public static function testCenters()
    {
        return array(
            'id' => 'testCenters',
            'url' => _url('index', 'TestCenter'),
            'label' => __('Home')
        );
    }
    
    /**
     * Create breadcrumb for TestCenter::testCenter
     *
     * @param core_kernel_classes_Resource $testCenter
     * @param array $testCenters
     * @return array
     */
    public static function testCenter(core_kernel_classes_Resource $testCenter, $testCenters = array())
    {
        //list also other available test centers
        $breadcrumbs = array(
            'id' => 'testCenter',
            'url' => _url('testCenter', 'TestCenter', null, array('testCenter' => $testCenter->getUri())),
            'label' => $testCenter->getLabel()
        );
    
        $otherTestSites = array_filter($testCenters, function($value) use ($testCenter) {
            return $value['id'] != $testCenter->getUri();
        });
    
            if (count($otherTestSites)) {
                $breadcrumbs['entries'] = $otherTestSites;
            }
    
            return $breadcrumbs;
    }

    /**
     * Create breadcrumb for Delivery::manage
     *
     * @param core_kernel_classes_Resource $testCenter
     * @param core_kernel_classes_Resource $delivery
     * @param string $page
     * @return array
     */
    public static function manageTestTakers(core_kernel_classes_Resource $testCenter, core_kernel_classes_Resource $delivery, $page)
    {
        $entries = array(
            array(
                'id' => 'manage',
                'url' => _url('manage', 'Delivery', null, array('testCenter' => $testCenter->getUri(), 'delivery' => $delivery->getUri())),
                'label' => __('Manage Test Takers')
            ),
            array(
                'id' => 'testTakers',
                'url' => _url('testTakers', 'Delivery', null, array('testCenter' => $testCenter->getUri(), 'delivery' => $delivery->getUri())),
                'label' => __('Add Test Takers')
            )
        );

        $currentPage = array_filter($entries, function($value) use($page) {
            return $value['id'] == $page;

        });

        $otherPages = array_filter($entries, function($value) use($page) {
            return $value['id'] != $page;

        });

        $breadcrumbs = current($currentPage);
        $breadcrumbs['entries'] = $otherPages;

        return $breadcrumbs;
    }

    /**
     * Create breadcrumb for Diagnostic::index
     *
     * @param core_kernel_classes_Resource $testCenter
     * @param array $alternativeRoutes
     * @return array
     */
    public static function diagnostics(core_kernel_classes_Resource $testCenter, $alternativeRoutes = array())
    {
        $breadcrumbs = array(
            'id' => 'diagnostics',
            'url' => _url('index', 'Diagnostic', null, array('testCenter' => $testCenter->getUri())),
            'label' => __('Readiness check')
        );
        if(count($alternativeRoutes)){
            $breadcrumbs['entries'] = $alternativeRoutes;
        }
        return $breadcrumbs;
    }
}