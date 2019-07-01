<?php

namespace oat\taoTestCenter\model\service;

use oat\oatbox\service\ConfigurableService;
use helpers_Random;

class OrganisationIdLabelStrategy extends ConfigurableService implements OrganisationIdLabelStrategyInterface
{
    const SERVICE_ID = 'taoTestCenter/OrganisationIdLabelStrategy';
    const DEFAULT_POSTFIX = 'Organisation Id';
    const POSTFIX_PARAM = 'postfix';
    const MAX_ATTEMPT = 100;

    /**
     * @inheritDoc
     */
    public function generateOrganisationId($testCenterLabel)
    {
        return $this->generateUnique($this->concatParts($testCenterLabel, $this->getPostfix()));
    }

    /**
     * @return string
     */
    private function getPostfix()
    {
        if ($this->hasOption(self::POSTFIX_PARAM)) {
            return $this->getOption(self::POSTFIX_PARAM);
        }
        return self::DEFAULT_POSTFIX;
    }

    /**
     * @param string $organisationId
     * @return string
     */
    private function generateUnique($organisationId)
    {
        $organisationService = $this->getOrganisationService();
        $count = 0;
        $unique = $organisationId;

        while ($organisationService->getTestCentersByOrganisationId($unique)) {
            if ($count > self::MAX_ATTEMPT) {
                return  $this->concatParts($organisationId, helpers_Random::generateString(8));
            }
            $unique = $this->concatParts($organisationId, ++$count);
        }
        return $unique;
    }

    /**
     * @param string $prefix
     * @param string $postfix
     * @return string
     */
    private function concatParts($prefix, $postfix)
    {
        return $prefix . ' ' . $postfix;
    }

    /**
     * @return OrganisationService
     */
    private function getOrganisationService()
    {
        return $this->getServiceLocator()->get(OrganisationService::SERVICE_ID);
    }
}
