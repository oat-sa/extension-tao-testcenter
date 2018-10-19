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
 * Copyright (c) 2018 Open Assessment Technologies SA
 */

namespace oat\taoTestCenter\scripts\tools;

use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\user\UserRdf;
use oat\oatbox\extension\script\ScriptAction;
use oat\tao\model\TaoOntology;
use oat\tao\model\user\TaoRoles;
use common_report_Report as Report;

class MappTestTakerToKV extends ScriptAction
{
    use OntologyAwareTrait;

    /**
     * @param $params
     * @throws \Exception
     */
    public function run()
    {
        $limit = $this->getOption('limit');
        $offset = $this->getOption('offset');

        $report = Report::createInfo('Mapping TestTakers logins');

        $class = $this->getClass(TaoOntology::CLASS_URI_SUBJECT);

        $tmpkvTable = new TmpKvTable();
        $this->propagate($tmpkvTable);
        $count = 0;
        do {

            $results = $class->searchInstances(
                [ UserRdf::PROPERTY_ROLES => TaoRoles::DELIVERY ],
                [
                    'like' => false,
                    'limit' => $limit,
                    'offset' => $offset
                ]
            );


            foreach ($results as $result) {
                try {
                    $value = $result->getUniquePropertyValue($this->getProperty(UserRdf::PROPERTY_LOGIN) );

                    $key   = $value->literal;
                    $value = $result->getUri();

                    if ($tmpkvTable->add($key, $value)){
                        $count++;
                    }

                } catch (\Exception $e) {
                    $report->add(Report::createFailure($e->getMessage()));
                }
            }

            $offset = $offset + $this->getOption('limit');

        }while(!empty($results));

        $report->add(Report::createSuccess('TT count mapped: ' . $count));

        return $report;
    }

    protected function provideOptions()
    {

        return [
            'limit' => [
                'prefix'      => 'l',
                'longPrefix'  => 'limit',
                'cast'        => 'integer',
                'required'    => true,
                'description' => 'Limit to get tt.'
            ],
            'offset' => [
                'prefix'      => 'o',
                'longPrefix'  => 'offset',
                'cast'        => 'integer',
                'required'    => true,
                'description' => 'Offset to get tt.'
            ]
        ];
    }

    protected function provideDescription()
    {
        return 'Mapping TestTakers logins';
    }
}