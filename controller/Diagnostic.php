<?php
/*
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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 *
 */

namespace oat\taoTestCenter\controller;

use oat\taoTestCenter\helper\TestCenterHelper;

/**
 * Proctoring Diagnostic controller for the readiness check screen
 *
 * @author Open Assessment Technologies SA
 * @package oat\taoTestCenter\controller
 * @license GPL-2.0
 *
 */
class Diagnostic extends SimplePageModule
{
    /**
     * Display the list of all readiness checks performed on the given test center
     * It also allows launching new ones.
     */
    public function index(){
        $testCenter = $this->getCurrentTestCenter();
        $requestOptions = $this->getRequestOptions();

        $this->setData('title', __('Readiness Check for test site %s', _dh($testCenter->getLabel())));
        $this->composeView(
            'diagnostic-index',
            array(
                'testCenter' => $testCenter->getUri(),
                'set' => TestCenterHelper::getDiagnostics($testCenter, $requestOptions),
                'config' => TestCenterHelper::getDiagnosticConfig($testCenter),
                'installedextension' => \common_ext_ExtensionsManager::singleton()->isInstalled('ltiDeliveryProvider'),
            ),
            'pages/index.tpl',
            'taoTestCenter'
        );
    }

    /**
     * Display the diagnostic runner
     */
    public function diagnostic()
    {
        $testCenter = $this->getCurrentTestCenter();

        $this->setData('title', __('Readiness Check for test site %s', $testCenter->getLabel()));
        $this->composeView(
            'diagnostic-runner',
            array(
                'testCenter' => $testCenter->getUri(),
                'config' => TestCenterHelper::getDiagnosticConfig($testCenter),
            ),
            'pages/index.tpl',
            'taoTestCenter'
        );
    }

    /**
     * Gets the list of diagnostic results
     *
     * @throws \common_Exception
     * @throws \oat\oatbox\service\ServiceNotFoundException
     */
    public function diagnosticData()
    {
        try {

            $testCenter = $this->getCurrentTestCenter();
            $requestOptions = $this->getRequestOptions();
            $this->returnJson(TestCenterHelper::getDiagnostics($testCenter, $requestOptions));

        } catch (ServiceNotFoundException $e) {
            \common_Logger::w('No diagnostic service defined for proctoring');
            $this->returnError('Proctoring interface not available');
        }
    }

    /**
     * Removes diagnostic results
     *
     * @throws \common_Exception
     */
    public function remove()
    {
        $testCenter = $this->getCurrentTestCenter();

        $id = $this->getRequestParameter('id');

        $this->returnJson([
            'success' => TestCenterHelper::removeDiagnostic($testCenter, $id)
        ]);
    }
    
    /**
     * Get the requested test center resource
     * Use this to identify which test center is currently being selected buy the proctor
     *
     * @return core_kernel_classes_Resource
     * @throws \common_Exception
     */
    protected function getCurrentTestCenter()
    {
        if($this->hasRequestParameter('testCenter')){
    
            //get test center resource from its uri
            $testCenterUri           = $this->getRequestParameter('testCenter');
            return TestCenterHelper::getTestCenter($testCenterUri);
        }else{
            //@todo use a better exception
            throw new \common_Exception('no current test center');
        }
    }
}
