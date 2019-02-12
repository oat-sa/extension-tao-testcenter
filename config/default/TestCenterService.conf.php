<?php

return new oat\taoTestCenter\model\TestCenterService(array(
    'roles_map' => array(
        'administrator' => array(
            'roleUri' => 'http://www.tao.lu/Ontologies/TAOProctor.rdf#ProctorRole',
            'propertyUri' => 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#administrator'
        ),
        'proctor' => array(
            'roleUri' => 'http://www.tao.lu/Ontologies/TAOProctor.rdf#TestCenterAdministratorRole',
            'propertyUri' => 'http://www.tao.lu/Ontologies/TAOTestCenter.rdf#assignedProctor'
        )
    )
));

