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

use oat\taoProctoring\controller\MonitorProctorAdministrator;
use oat\taoTestCenter\controller\Import;
use oat\taoTestCenter\scripts\install\RegisterTestCenterEntryPoint;
use oat\taoTestCenter\controller\TestCenterManager;
use oat\taoTestCenter\controller\TestCenter;
use oat\taoTestCenter\scripts\install\TestCenterOverrideServices;
use oat\taoTestCenter\controller\ProctorManager;
use oat\taoTestCenter\controller\Diagnostic;
use oat\taoTestCenter\controller\RestEligibilities;
use oat\taoTestCenter\controller\RestEligibility;
use oat\taoTestCenter\controller\RestTestCenter;
use oat\taoTestCenter\scripts\install\RegisterTestCenterEvents;
use oat\taoProctoring\model\ProctorService;
use oat\taoTestCenter\model\TestCenterService;
use oat\taoTestCenter\scripts\install\OverrideBreadcrumbsServices;
use oat\taoTestCenter\scripts\install\RegisterClientLibConfig;
use oat\taoTestCenter\controller\RestTestCenterUsers;

return array(
    'name' => 'taoTestCenter',
    'label' => 'Test Center',
    'description' => 'Proctoring via test-centers',
    'license' => 'GPL-2.0',
    'version' => '4.8.1',
    'author' => 'Open Assessment Technologies SA',
    'requires' => array(
        'taoProctoring'  => '>=11.0.0',
        'taoDelivery'    => '>=11.0.0',
        'generis'        => '>=7.4.0',
        'tao'            => '>=27.0.0',
        'taoTestTaker'   => '>=4.0.0',
        'taoDeliveryRdf' => '>=6.0.0',
    ),
    'managementRole' => TestCenterService::ROLE_TESTCENTER_MANAGER,
    'acl' => array(
        array('grant', TestCenterService::ROLE_TESTCENTER_MANAGER, TestCenterManager::class),
        array('grant', TestCenterService::ROLE_TESTCENTER_ADMINISTRATOR, ProctorManager::class),
        array('grant', TestCenterService::ROLE_TESTCENTER_MANAGER, Import::class),
        array('grant', TestCenterService::ROLE_TESTCENTER_ADMINISTRATOR, Import::class),
        array('grant', ProctorService::ROLE_PROCTOR, TestCenter::class),
        array('grant', ProctorService::ROLE_PROCTOR, Diagnostic::class),
        array('grant', TestCenterService::ROLE_TESTCENTER_ADMINISTRATOR, MonitorProctorAdministrator::class),
        array('grant', TestCenterService::ROLE_TESTCENTER_MANAGER, RestEligibility::class),
        array('grant', TestCenterService::ROLE_TESTCENTER_MANAGER, RestEligibilities::class),
        array('grant', TestCenterService::ROLE_TESTCENTER_MANAGER, RestTestCenter::class),
        array('grant', TestCenterService::ROLE_TESTCENTER_MANAGER, RestTestCenterUsers::class),
        //array('grant', TaoRoles::ANONYMOUS, DiagnosticChecker::class),
    ),
    'install' => array(
        'php' => array(
            RegisterTestCenterEntryPoint::class,
            TestCenterOverrideServices::class,
            RegisterTestCenterEvents::class,
            OverrideBreadcrumbsServices::class,
            RegisterClientLibConfig::class,
        ),
        'rdf' => array(
            __DIR__.'/scripts/install/ontology/taotestcenter.rdf',
            __DIR__.'/scripts/install/ontology/eligibility.rdf',
        )
    ),
//    'uninstall' => array(),
    'update' => 'oat\\taoTestCenter\\scripts\\update\\Updater',
    'routes' => array(
        '/taoTestCenter/api' => ['class' => \oat\taoTestCenter\model\routing\ApiRoute::class],
        '/taoTestCenter' => 'oat\\taoTestCenter\\controller',
    ),
    'constants' => array(
        # views directory
        "DIR_VIEWS" => dirname(__FILE__) . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR,
        #BASE URL (usually the domain root)
        'BASE_URL' => ROOT_URL . 'taoTestCenter/',
    ),
    'extra' => array(
        'structures' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'structures.xml',
    )
);
