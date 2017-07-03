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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-2014 (update and modification) Open Assessment Technologies SA
 */

namespace oat\taoTestCenter\scripts\update;

use oat\taoProctoring\model\ProctorService;
use oat\taoProctoring\model\ProctorServiceRoute;
use oat\taoTestCenter\model\proctoring\TestCenterProctorService;

/**
 *
 * @access public
 * @package taoGroups
 */
class Updater extends \common_ext_ExtensionUpdater
{

    /**
     * (non-PHPdoc)
     * @see common_ext_ExtensionUpdater::update()
     */
    public function update($initialVersion)
    {
        if ($this->isBetween('0.0.1', '0.3.0')) {
            throw new \common_Exception('Upgrade unavailable');
        }

        $this->skip('0.3.0', '2.0.1');

        if ($this->isVersion('2.0.1')) {
            // to avoid configuration overwrite
            if (!$this->getServiceManager()->has(ProctorService::SERVICE_ID)
                || !is_a($this->getServiceManager()->get(ProctorService::SERVICE_ID), ProctorServiceRoute::class)
                ) {

                $this->getServiceManager()->register(ProctorService::SERVICE_ID, new ProctorServiceRoute());
            }

            $proctorService = $this->getServiceManager()->get(ProctorService::SERVICE_ID);
            $config = $proctorService->getOptions();
            if (!isset($config[ProctorServiceRoute::PROCTOR_SERVICE_ROUTES])) {
                $config[ProctorServiceRoute::PROCTOR_SERVICE_ROUTES] = [];
            }
            $config[ProctorServiceRoute::PROCTOR_SERVICE_ROUTES][] = TestCenterProctorService::class;
            $config[ProctorServiceRoute::PROCTOR_SERVICE_ROUTES] = array_unique($config[ProctorServiceRoute::PROCTOR_SERVICE_ROUTES]);
            $this->getServiceManager()->register(ProctorService::SERVICE_ID, new ProctorServiceRoute($config));

            $this->setVersion('2.1.0');
        }
    }
}
