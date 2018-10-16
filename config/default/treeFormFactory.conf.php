<?php
return new \oat\taoTestCenter\model\gui\form\TreeFormFactory(array(
    'formFactories' => array(
        new \oat\taoTestCenter\model\gui\TestcenterAdministratorUserFormFactory(array(
            'property' => \oat\taoTestCenter\model\ProctorManagementService::PROPERTY_ADMINISTRATOR_URI,
            'title' => 'Assign administrators',
            'isReversed' => true,
        )),
        new \oat\taoTestCenter\model\gui\ProctorUserFormFactory(array(
            'property' => \oat\taoTestCenter\model\ProctorManagementService::PROPERTY_ASSIGNED_PROCTOR_URI,
            'title' => 'Assign proctors',
            'isReversed' => true,
        )),
        new \oat\taoTestCenter\model\gui\form\formFactory\SubTestCenterFormFactory(array(
            'property' => \oat\taoTestCenter\model\TestCenterService::PROPERTY_CHILDREN_URI,
            'title' => 'Define sub-centers',
            'isReversed' => false,
        )),
    )
));