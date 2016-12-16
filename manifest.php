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
use oat\taoTestCenter\scripts\install\RegisterTestCenterEntryPoint;
use oat\taoTestCenter\controller\TestCenterManager;
use oat\taoTestCenter\controller\TestCenter;
use oat\taoTestCenter\scripts\install\RegisterEligibilityService;
use oat\taoTestCenter\scripts\install\RegisterAssignmentService;
use oat\taoTestCenter\controller\ProctorManager;
use oat\taoTestCenter\scripts\install\CreateDiagnosticTable;
use oat\taoTestCenter\controller\DiagnosticChecker;
use oat\taoTestCenter\controller\Diagnostic;

return array(
    'name' => 'taoTestCenter',
    'label' => 'Test Center',
    'description' => 'Proctoring via test-centers',
    'license' => 'GPL-2.0',
    'version' => '0.3.0',
    'author' => 'Open Assessment Technologies SA',
    'requires' => array(
        'taoProctoring' => '>=3.15.0',
        'taoClientDiagnostic' => '>=1.11.0'
    ),
    'acl' => array(
        array('grant', 'http://www.tao.lu/Ontologies/TAOProctor.rdf#TestCenterManager', array('controller' => TestCenterManager::class)),
        array('grant', 'http://www.tao.lu/Ontologies/TAOProctor.rdf#TestCenterAdministratorRole', array('controller' => ProctorManager::class)),
        array('grant', 'http://www.tao.lu/Ontologies/TAOProctor.rdf#ProctorRole', array('controller' => TestCenter::class)),
        array('grant', 'http://www.tao.lu/Ontologies/TAOProctor.rdf#ProctorRole', array('controller' => Diagnostic::class)),
        array('grant', 'http://www.tao.lu/Ontologies/generis.rdf#taoClientDiagnosticManager', array('controller' => DiagnosticChecker::class)),
        array('grant', 'http://www.tao.lu/Ontologies/generis.rdf#AnonymousRole', array('controller' => DiagnosticChecker::class)),
    ),
    'install' => array(
        'php' => array(
            RegisterTestCenterEntryPoint::class,
            RegisterEligibilityService::class,
            RegisterAssignmentService::class,
            CreateDiagnosticTable::class
        ),
        'rdf' => array(
            __DIR__.'/scripts/install/ontology/taotestcenter.rdf',
            __DIR__.'/scripts/install/ontology/eligibility.rdf',
        )
    ),
    'uninstall' => array(),
    'update' => 'oat\\taoTestCenter\\scripts\\update\\Updater',
    'routes' => array(
        '/taoTestCenter' => 'oat\\taoTestCenter\\controller'
    ),
    'constants' => array(
        # views directory
        "DIR_VIEWS" => dirname(__FILE__) . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR,
        #BASE URL (usually the domain root)
        'BASE_URL' => ROOT_URL . 'taoTestCenter/',
        #BASE WWW required by JS
        'BASE_WWW' => ROOT_URL . 'taoTestCenter/views/'
    ),
    'extra' => array(
        'structures' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'structures.xml',
    )
);