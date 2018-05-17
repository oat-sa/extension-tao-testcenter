<?php
/**
 * Default config header created during install
 */

use oat\generis\model\user\UserRdf;
use oat\tao\model\import\service\ArrayImportValueMapper;
use oat\tao\model\import\service\RdsValidatorValueMapper;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoTestCenter\model\EligibilityService;
use oat\taoTestCenter\model\TestCenterService;

return new oat\taoTestCenter\model\import\EligibilityCsvImporterFactory(array(
    'default-schema' => array(
        'mandatory' => array(
            'test center' => [
                EligibilityService::PROPERTY_TESTCENTER_URI => new RdsValidatorValueMapper([
                    RdsValidatorValueMapper::OPTION_CLASS  => TestCenterService::CLASS_URI,
                ])
            ],
            'delivery' => [
                EligibilityService::PROPERTY_DELIVERY_URI => new RdsValidatorValueMapper([
                    RdsValidatorValueMapper::OPTION_CLASS => DeliveryAssemblyService::CLASS_URI,
                ])
            ],
            'test takers' => [
                EligibilityService::PROPERTY_TESTTAKER_URI => new ArrayImportValueMapper([
                    ArrayImportValueMapper::OPTION_DELIMITER => '|',
                    ArrayImportValueMapper::OPTION_VALUE_MAPPER => new RdsValidatorValueMapper([
                        RdsValidatorValueMapper::OPTION_CLASS => UserRdf::CLASS_URI,
                        RdsValidatorValueMapper::OPTION_PROPERTY  => UserRdf::PROPERTY_LOGIN
                    ])
                ])
            ],
        ),
        'optional' => [
            'is proctored' => EligibilityService::PROPERTY_BYPASSPROCTOR_URI
        ]
    ),
    'mappers' => array(
        'default' => array(
            'importer' => new oat\taoTestCenter\model\import\RdsEligibilityImportService()
        )
    )
));
