<?php

declare(strict_types=1);

namespace oat\taoTestCenter\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\model\search\SearchProxy;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoTestCenter\model\TestCenterService;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202209260910021871_taoTestCenter extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Add taoTestCenter to OPTION_GENERIS_SEARCH_WHITELIST';
    }

    public function up(Schema $schema): void
    {
        /** @var SearchProxy $searchProxy */
        $searchProxy = $this->getServiceManager()->get(SearchProxy::SERVICE_ID);

        $searchProxy->extendGenerisSearchWhiteList(
            [TestCenterService::CLASS_URI]
        );

        $this->registerService(SearchProxy::SERVICE_ID, $searchProxy);

    }

    public function down(Schema $schema): void
    {
        /** @var SearchProxy $searchProxy */
        $searchProxy = $this->getServiceManager()->get(SearchProxy::SERVICE_ID);

        $searchProxy->removeFromGenerisSearchWhiteList(
            [TestCenterService::CLASS_URI]
        );

        $this->registerService(SearchProxy::SERVICE_ID, $searchProxy);
    }
}
