<?php

declare(strict_types=1);

namespace oat\taoTestCenter\migrations;

use Doctrine\DBAL\Schema\Schema;
use core_kernel_classes_Property;
use oat\oatbox\reporting\Report;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoTestCenter\model\TestCenterAssignment;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * phpcs:disable Squiz.Classes.ValidClassName
 */
final class Version202402190728491871_taoTestCenter extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update model for the UserAssignment property and regenerate cache';
    }

    public function up(Schema $schema): void
    {
        // Sync models for applying changes for the UserAssignment property with Multiple=true
        OntologyUpdater::syncModels();
        $this->addReport(Report::createSuccess('Models were successfully synchronized'));
        // Regenerate cache for the UserAssignment property with proper PropIsMultiple_ value
        $property = new core_kernel_classes_Property(TestCenterAssignment::PROPERTY_TESTTAKER_ASSIGNED);
        $property->clearCachedValues();
        $property->warmupCachedValues();
        $this->addReport(Report::createSuccess('Cache of property values was successfully updated'));
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
