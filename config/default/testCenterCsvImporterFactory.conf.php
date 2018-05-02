в<?php
/**
 * Default config header created during install
 */

return new \oat\taoTestCenter\model\import\TestCenterCsvImporterFactory([
    'mappers' => [
        'default' => array(
            'importer' => new \oat\taoTestCenter\model\import\RdsTestCenterImportService()
        ),
    ],
    'default-schema' => [
        'mandatory' => [
            'label' => 'http://www.w3.org/2000/01/rdf-schema#label',
        ],
        'optional' => []
    ]
]);
