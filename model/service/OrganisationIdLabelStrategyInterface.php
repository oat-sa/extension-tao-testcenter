<?php

namespace oat\taoTestCenter\model\service;

interface OrganisationIdLabelStrategyInterface
{
    /**
     * @param string $originValue
     * @return string
     */
    public function generateOrganisationId($originValue);
}
