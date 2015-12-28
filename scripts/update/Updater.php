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

use oat\tao\scripts\update\OntologyUpdater;
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
        $current = $initialVersion;
        
        if ($current == '0.0.1' || $current == '0.1.0' || $current == '0.1.1') {
            OntologyUpdater::syncModels();
            $current = '0.1.2';
        }

        if ($current == '0.1.2') {
            OntologyUpdater::syncModels();

            $accessService = \funcAcl_models_classes_AccessService::singleton();
            $roleService = \tao_models_classes_RoleService::singleton();

            $testCenterManager = new \core_kernel_classes_Resource('http://www.tao.lu/Ontologies/generis.rdf#TestCenterManager');
            //revoke access right to test center manager
            $accessService->revokeExtensionAccess($testCenterManager, 'taoTestCenter');
            $roleService->removeRole($testCenterManager);

            $current = '0.2';
        }

        return $current;
    }
}
