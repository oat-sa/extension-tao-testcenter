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

namespace oat\taoTestCenter\scripts\tools;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\extension\script\ScriptAction;
use common_report_Report as Report;
use oat\taoTestCenter\model\EligibilityService;

/**
 * sudo -u www-data php index.php 'oat\taoTestCenter\scripts\tools\CleanupEligibility'
 */
class CleanupEligibility extends ScriptAction
{
    use OntologyAwareTrait;

    protected function provideOptions()
    {
        return [];
    }

    protected function provideDescription()
    {
        return 'Cleanup Eligibilities.';
    }

    /**
     * @return Report
     * @throws \common_exception_Error
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    protected function run()
    {
        $report = new Report(Report::TYPE_INFO, __('Clean up orphan eligibilities'));
        $eligibilityService = $this->getServiceManager()->get(EligibilityService::class);
        $eligibilities = $eligibilityService->getRootClass()->getInstances(true);
        $testCenterProp = $this->getProperty(EligibilityService::PROPERTY_TESTCENTER_URI);
        $i = 0;

        foreach ($eligibilities as $eligibility) {
            $tc = $eligibility->getOnePropertyValue($testCenterProp);
            if (!$tc->exists()) {
                $i++;
                $eligibility->delete(true);
            }
        }

        $report->add(Report::createSuccess(__('%d eligibilities have been deleted', $i)));

        return $report;
    }

}