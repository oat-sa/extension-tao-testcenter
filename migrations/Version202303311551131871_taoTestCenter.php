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
 */

//declare(strict_types=1);

namespace oat\taoTestCenter\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use core_kernel_classes_Resource as RdfResource;
use tao_models_classes_RoleService as RoleService;

final class Version202303311551131871_taoTestCenter extends AbstractMigration
{
    use OntologyAwareTrait;

    public function getDescription(): string
    {
        return 'Remove taoGroups access when taoTestCenter is installed.';
    }

    public function up(Schema $schema): void
    {
        var_dump($this->globalManagerHasGroupManagerRole());
        if ($this->globalManagerHasGroupManagerRole()) {
            $this->getRoleService()->unincludeRole($this->getGlobalManagerRole(), $this->getGroupManagerRole());
        }

        $this->logInfo("taoGroupManager role detached from GlobalManager.");
    }

    public function down(Schema $schema): void
    {
        if (!$this->globalManagerHasGroupManagerRole()) {
            $this->getRoleService()->includeRole($this->getGlobalManagerRole(), $this->getGroupManagerRole());
        }

        $this->logInfo("taoGroupManager role attached to GlobalManager.");
    }

    private function getGlobalManagerRole(): RdfResource
    {
        return $this->getResource(TaoRoles::GLOBAL_MANAGER);
    }

    private function getGroupManagerRole(): RdfResource
    {
        return $this->getResource('http://www.tao.lu/Ontologies/TAOGroup.rdf#GroupsManagerRole');
    }

    private function globalManagerHasGroupManagerRole(): bool
    {
        return in_array(
            $this->getGroupManagerRole(),
            $this->getRoleService()->getIncludedRoles($this->getGlobalManagerRole())
        );
    }

    private function getRoleService(): RoleService
    {
        return RoleService::singleton();
    }
}
