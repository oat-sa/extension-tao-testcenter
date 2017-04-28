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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
namespace oat\taoTestCenter\scripts\install;

use oat\taoClientDiagnostic\model\authorization\Anonymous;
use oat\taoClientDiagnostic\model\authorization\Authorization;
use oat\taoClientDiagnostic\model\storage\Storage;
use oat\oatbox\extension\InstallAction;
use oat\taoClientDiagnostic\model\storage\PaginatedSqlStorage;

class AddDiagnosticSettings extends InstallAction
{
    public function __invoke($params)
    {
        //Set diagnostic config
        $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoClientDiagnostic');
        $config = $extension->getConfig('clientDiag');
        $extension->setConfig('clientDiag', array_merge($config, array(
            'performances' => array(
                'samples' => array(
                    'taoClientDiagnostic/tools/performances/data/sample1/',
                    'taoClientDiagnostic/tools/performances/data/sample2/',
                    'taoClientDiagnostic/tools/performances/data/sample3/'
                ),
                'occurrences' => 10,
                'timeout' => 30,
                'optimal' => 0.05,
                'threshold' => 0.75
            ),
            'bandwidth' => array(
                'unit' => 0.16,
                'ideal' => 45,
                'max' => 100,
            ),
        )));

        //Set diagnostic authorization
        $this->registerService(Authorization::SERVICE_ID, new Anonymous());

        //Set diagnostic storage
        $storageService = new PaginatedSqlStorage(['persistence' => 'default']);
        $this->registerService(Storage::SERVICE_ID, $storageService);
        
        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Diagnostic settings added to Proctoring extension');
    }
}
