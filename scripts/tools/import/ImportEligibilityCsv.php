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
 */

namespace oat\taoTestCenter\scripts\tools\import;

use oat\oatbox\extension\script\ScriptAction;
use oat\taoTestCenter\model\import\EligibilityCsvImporterFactory;
use oat\taoTestCenter\model\import\TestCenterCsvImporterFactory;

/**
 * sudo -u www-data php index.php 'oat\taoTestCenter\scripts\tools\import\ImportEligibilityCsv' -f /txt.csv
 */
class ImportEligibilityCsv extends ScriptAction
{
    protected function provideOptions()
    {
        return [
            'file-path' => [
                'prefix' => 'f',
                'longPrefix' => 'file-path',
                'required' => true,
                'description' => 'File path location.',
            ],

        ];
    }

    protected function provideDescription()
    {
        return 'Import Eligibilities.';
    }

    /**
     * @return \common_report_Report
     * @throws \common_exception_NotFound
     * @throws \oat\oatbox\service\exception\InvalidService
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    protected function run()
    {
        /** @var EligibilityCsvImporterFactory $eligImport */
        $eligImport = $this->getServiceLocator()->get(EligibilityCsvImporterFactory::SERVICE_ID);
        $importer = $eligImport->create('default');

        return $importer->import($this->getOption('file-path'), []);
    }

}