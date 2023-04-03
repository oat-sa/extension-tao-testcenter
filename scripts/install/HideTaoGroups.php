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
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\taoTestCenter\scripts\install;

use core_kernel_classes_Resource;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\user\TaoRoles;
use tao_models_classes_RoleService;

class HideTaoGroups extends InstallAction
{
    public function __invoke($params)
    {
        self::updateACL(false);
    }

    public static function updateACL(bool $include)
    {
        $role = new core_kernel_classes_Resource('http://www.tao.lu/Ontologies/TAOGroup.rdf#GroupsManagerRole');
        if ($role->exists()) {
            $globalManagerRole = new core_kernel_classes_Resource(TaoRoles::GLOBAL_MANAGER);
            $roleService = tao_models_classes_RoleService::singleton();
            if ($include) {
                $roleService->includeRole($globalManagerRole, $role);
            } else {
                $roleService->unincludeRole($globalManagerRole, $role);
            }
        }
    }
}
