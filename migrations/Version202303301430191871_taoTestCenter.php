<?php

declare(strict_types=1);

namespace oat\taoTestCenter\migrations;

use core_kernel_classes_Resource;
use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;
use oat\tao\scripts\tools\accessControl\SetRolesAccess;
use oat\taoGroups\controller\Groups;
use oat\taoItems\model\user\TaoItemsRoles;
use tao_models_classes_RoleService;

final class Version202303301430191871_taoTestCenter extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove taoGroups role from GlobalManager role because of collision between Groups and TestCenter extensions';
    }

    public function up(Schema $schema): void
    {
        $this->updateACL(false);
    }

    private function updateACL(bool $include)
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

    public function down(Schema $schema): void
    {
        $this->updateACL(true);
    }
}
