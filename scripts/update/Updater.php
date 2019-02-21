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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2013-2014 (update and modification) Open Assessment Technologies SA
 */

namespace oat\taoTestCenter\scripts\update;

use common_Exception;
use common_ext_ExtensionUpdater;
use oat\generis\model\OntologyRdfs;
use oat\generis\model\user\UserRdf;
use oat\oatbox\event\EventManager;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\event\UserRemovedEvent;
use oat\tao\model\import\service\ArrayImportValueMapper;
use oat\tao\model\import\service\ImportMapperInterface;
use oat\tao\model\import\service\RdsValidatorValueMapper;
use oat\tao\model\user\import\UserCsvImporterFactory;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoProctoring\controller\MonitorProctorAdministrator;
use oat\taoProctoring\model\authorization\TestTakerAuthorizationInterface;
use oat\taoProctoring\model\ProctorServiceInterface;
use oat\taoTestCenter\controller\Import;
use oat\taoTestCenter\controller\RestEligibilities;
use oat\taoTestCenter\controller\RestEligibility;
use oat\taoTestCenter\controller\RestTestCenter;
use oat\taoTestCenter\controller\RestTestCenterUsers;
use oat\taoTestCenter\model\breadcrumbs\OverriddenDeliverySelectionService;
use oat\taoTestCenter\model\breadcrumbs\OverriddenMonitorService;
use oat\taoTestCenter\model\breadcrumbs\OverriddenReportingService;
use oat\taoTestCenter\model\EligibilityService;
use oat\taoTestCenter\model\gui\form\formFactory\FormFactory;
use oat\taoTestCenter\model\gui\form\formFactory\SubTestCenterFormFactory;
use oat\taoTestCenter\model\gui\form\TreeFormFactory;
use oat\taoTestCenter\model\gui\TestcenterAdministratorUserFormFactory;
use oat\taoTestCenter\model\gui\ProctorUserFormFactory;
use oat\taoTestCenter\model\import\EligibilityCsvImporterFactory;
use oat\taoTestCenter\model\import\RdsEligibilityImportService;
use oat\taoTestCenter\model\import\RdsTestCenterImportService;
use oat\taoTestCenter\model\import\TestCenterAdminCsvImporter;
use oat\taoTestCenter\model\import\TestCenterCsvImporterFactory;
use oat\taoTestCenter\model\proctoring\TestCenterAuthorizationService;
use oat\taoTestCenter\model\proctoring\TestCenterProctorService;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoTestCenter\model\ProctorManagementService;
use oat\taoTestCenter\model\TestCenterService;
use oat\taoTestTaker\models\events\TestTakerRemovedEvent;
use oat\tao\model\ClientLibConfigRegistry;
use common_report_Report as Report;
use oat\taoTestCenter\scripts\tools\CleanupEligibility;

/**
 *
 * @access public
 * @package taoGroups
 */
class Updater extends common_ext_ExtensionUpdater
{
    /**
     * (non-PHPdoc)
     * @see common_ext_ExtensionUpdater::update()
     * @throws common_Exception
     */
    public function update($initialVersion)
    {
        if ($this->isBetween('0.0.1', '0.3.0')) {
            throw new common_Exception('Upgrade unavailable');
        }

        $this->skip('0.3.0', '2.0.2');

        if ($this->isVersion('2.0.2')) {
            OntologyUpdater::syncModels();
            $this->setVersion('2.0.3');
        }

        $this->skip('2.0.3', '2.0.4');

        if ($this->isVersion('2.0.4')) {
            $delegator = $this->getServiceManager()->get(ProctorServiceInterface::SERVICE_ID);
            $delegator->registerHandler(new TestCenterProctorService());
            $this->getServiceManager()->register(ProctorServiceInterface::SERVICE_ID, $delegator);
            $this->setVersion('2.1.0');
        }

        if ($this->isVersion('2.1.0')) {
            $delegator = $this->getServiceManager()->get(TestTakerAuthorizationInterface::SERVICE_ID);
            $delegator->registerHandler(new TestCenterAuthorizationService());
            $this->getServiceManager()->register(TestTakerAuthorizationInterface::SERVICE_ID, $delegator);

            $this->setVersion('3.0.0');
        }

        $this->skip('3.0.0', '3.0.1');

        if ($this->isVersion('3.0.1')) {
            $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
            $eventManager->attach(UserRemovedEvent::EVENT_NAME, [EligibilityService::SERVICE_ID, 'deletedTestTaker']);
            $eventManager->attach(TestTakerRemovedEvent::EVENT_NAME, [EligibilityService::SERVICE_ID, 'deletedTestTaker']);
            $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

            $this->setVersion('3.1.0');
        }

        $this->skip('3.1.0', '3.1.2');

        if ($this->isVersion('3.1.2')) {
            $this->getServiceManager()->register(
                OverriddenDeliverySelectionService::SERVICE_ID,
                new OverriddenDeliverySelectionService()
            );

            $this->setVersion('3.2.0');
        }
        $this->skip('3.2.0', '3.2.3');

        if ($this->isVersion('3.2.3')) {
            ClientLibConfigRegistry::getRegistry()->register('taoTestCenter/component/eligibilityEditor', [
                'deliveriesOrder' => 'http://www.w3.org/2000/01/rdf-schema#label',
                'deliveriesOrderdir' => 'asc',
            ]);

            $this->setVersion('3.3.0');
        }

        $this->skip('3.3.0', '3.8.0');

        if ($this->isVersion('3.8.0')) {
            /** @var UserCsvImporterFactory $importerFactory */
            $importerFactory = $this->getServiceManager()->get(UserCsvImporterFactory::SERVICE_ID);
            $typeOptions = $importerFactory->getOption(UserCsvImporterFactory::OPTION_MAPPERS);
            $typeOptions[TestCenterAdminCsvImporter::USER_IMPORTER_TYPE] = array(
                UserCsvImporterFactory::OPTION_MAPPERS_IMPORTER => new TestCenterAdminCsvImporter()
            );
            $importerFactory->setOption(UserCsvImporterFactory::OPTION_MAPPERS, $typeOptions);
            $this->getServiceManager()->register(UserCsvImporterFactory::SERVICE_ID, $importerFactory);

            $this->setVersion('3.9.0');
        }

        if ($this->isVersion('3.9.0')) {
            AclProxy::applyRule(
                new AccessRule(
                    AccessRule::GRANT,
                    TestCenterService::ROLE_TESTCENTER_MANAGER,
                    Import::class
                )
            );

            AclProxy::applyRule(
                new AccessRule(
                    AccessRule::GRANT,
                    TestCenterService::ROLE_TESTCENTER_ADMINISTRATOR,
                    Import::class
                )
            );

            $service = new TestCenterCsvImporterFactory(array(
                TestCenterCsvImporterFactory::OPTION_DEFAULT_SCHEMA => array(
                    ImportMapperInterface::OPTION_SCHEMA_MANDATORY => [
                        'label' => OntologyRdfs::RDFS_LABEL,
                    ],
                    ImportMapperInterface::OPTION_SCHEMA_OPTIONAL => []
                )
            ));
            $typeOptions = [];
            $typeOptions['default'] = array(
                TestCenterCsvImporterFactory::OPTION_MAPPERS_IMPORTER => new RdsTestCenterImportService()
            );
            $service->setOption(TestCenterCsvImporterFactory::OPTION_MAPPERS, $typeOptions);

            $this->getServiceManager()->register(TestCenterCsvImporterFactory::SERVICE_ID, $service);

            $this->setVersion('3.10.0');
        }

        $this->skip('3.10.0', '3.11.0');

        if ($this->isVersion('3.11.0')) {
            /** @var TestCenterCsvImporterFactory $serviceTestCenterImporter */
            $serviceTestCenterImporter = $this->getServiceManager()->get(TestCenterCsvImporterFactory::SERVICE_ID);
            $schema = $serviceTestCenterImporter->getOption(TestCenterCsvImporterFactory::OPTION_DEFAULT_SCHEMA);
            $schema['optional'] = [
                'administrators' =>[
                    ProctorManagementService::PROPERTY_ADMINISTRATOR_URI => new ArrayImportValueMapper([
                        'delimiter' => '|',
                        'valueMapper' => new RdsValidatorValueMapper([
                            'class' => UserRdf::CLASS_URI,
                            'property' => UserRdf::PROPERTY_LOGIN
                        ])
                    ])
                ],
                'proctors' =>[
                    ProctorManagementService::PROPERTY_ASSIGNED_PROCTOR_URI => new ArrayImportValueMapper([
                        'delimiter' => '|',
                        'valueMapper' => new RdsValidatorValueMapper([
                            'class' => UserRdf::CLASS_URI,
                            'property' => UserRdf::PROPERTY_LOGIN
                        ])
                    ])
                ],
                'sub centers' => [
                    TestCenterService::PROPERTY_CHILDREN_URI => new ArrayImportValueMapper([
                        'delimiter' => '|',
                        'valueMapper' => new RdsValidatorValueMapper([
                            'class' => TestCenterService::CLASS_URI,
                            'property' => OntologyRdfs::RDFS_LABEL
                        ])
                    ])
                ]
            ];
            $serviceTestCenterImporter->setOption(TestCenterCsvImporterFactory::OPTION_DEFAULT_SCHEMA, $schema);
            $this->getServiceManager()->register(TestCenterCsvImporterFactory::SERVICE_ID, $serviceTestCenterImporter);

            $service = new EligibilityCsvImporterFactory([
                EligibilityCsvImporterFactory::OPTION_DEFAULT_SCHEMA => [
                    ImportMapperInterface::OPTION_SCHEMA_MANDATORY => [
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
                    ],
                    ImportMapperInterface::OPTION_SCHEMA_OPTIONAL => [
                        'is proctored' => EligibilityService::PROPERTY_BYPASSPROCTOR_URI
                    ]
                ],
                EligibilityCsvImporterFactory::OPTION_MAPPERS => [
                    'default' => [
                        EligibilityCsvImporterFactory::OPTION_MAPPERS_IMPORTER => new RdsEligibilityImportService()
                    ]
                ]
            ]);

            $this->getServiceManager()->register(EligibilityCsvImporterFactory::SERVICE_ID, $service);

            $this->setVersion('3.12.0');
        }

        $this->skip('3.12.0', '3.13.1');

        if ($this->isVersion('3.13.1')) {
            AclProxy::applyRule(
                new AccessRule(
                    'grant',
                    TestCenterService::ROLE_TESTCENTER_ADMINISTRATOR,
                    MonitorProctorAdministrator::class
                )
            );

            $this->setVersion('3.14.0');
        }

        $this->skip('3.14.0', '3.14.2');

        if ($this->isVersion('3.14.2')) {
            $this->getServiceManager()->register(TreeFormFactory::SERVICE_ID, new TreeFormFactory(array(
                TreeFormFactory::OPTION_FORM_FACTORIES => array(
                    new FormFactory(array(
                        'property' => ProctorManagementService::PROPERTY_ADMINISTRATOR_URI,
                        'title' => 'Assign administrators',
                        'isReversed' => true,
                    )),
                    new FormFactory(array(
                        'property' => ProctorManagementService::PROPERTY_ASSIGNED_PROCTOR_URI,
                        'title' => 'Assign proctors',
                        'isReversed' => true,
                    )),
                    new SubTestCenterFormFactory(array(
                        'property' => TestCenterService::PROPERTY_CHILDREN_URI,
                        'title' => 'Define sub-centers',
                        'isReversed' => false,
                    )),
                )
            )));
            $this->setVersion('3.15.0');
        }

        if ($this->isVersion('3.15.0')) {
            $this->addReport(new Report(
                Report::TYPE_WARNING,
                __('Please run %s script to clean up orphan eligibilities', CleanupEligibility::class)
            ));
            $this->setVersion('3.16.0');
        }

        if ($this->isVersion('3.16.0')) {
            AclProxy::applyRule(
                new AccessRule(
                    'grant',
                    TestCenterService::ROLE_TESTCENTER_MANAGER,
                    RestEligibility::class
                )
            );

            $this->setVersion('3.17.0');
        }

        if ($this->isVersion('3.17.0')) {
            AclProxy::applyRule(
                new AccessRule(
                    'grant',
                    TestCenterService::ROLE_TESTCENTER_MANAGER,
                    RestTestCenter::class
                )
            );

            $this->setVersion('3.18.0');
        }

        $this->skip('3.18.0', '3.18.1');

        if ($this->isVersion('3.18.1')) {
            $service = $this->getServiceManager()->get(TreeFormFactory::SERVICE_ID);
            $factories = $service->getOption(TreeFormFactory::OPTION_FORM_FACTORIES);
            /** @var FormFactory $factory */
            foreach ($factories as &$factory) {
                if (ProctorManagementService::PROPERTY_ASSIGNED_PROCTOR_URI === $factory->getOption('property')) {
                    $factory = new ProctorUserFormFactory(array(
                        'property' => ProctorManagementService::PROPERTY_ASSIGNED_PROCTOR_URI,
                        'title' => 'Assign proctors',
                        'isReversed' => true
                    ));
                }

                if (ProctorManagementService::PROPERTY_ADMINISTRATOR_URI === $factory->getOption('property')) {
                    $factory = new TestcenterAdministratorUserFormFactory(array(
                        'property' => ProctorManagementService::PROPERTY_ADMINISTRATOR_URI,
                        'title' => 'Assign administrators',
                        'isReversed' => true
                    ));
                }
            }
            $service->setOption(TreeFormFactory::OPTION_FORM_FACTORIES, $factories);
            $this->getServiceManager()->register(TreeFormFactory::SERVICE_ID, $service);

            $this->setVersion('3.19.0');
        }

        $this->skip('3.19.0', '4.3.2');

        if ($this->isVersion('4.3.2')) {
            $this->getServiceManager()->register(TestCenterService::SERVICE_ID, new TestCenterService([]));
            $this->setVersion('4.4.1');
        }

        if ($this->isVersion('4.4.1')) {
            AclProxy::applyRule(
                new AccessRule(
                    'grant',
                    TestCenterService::ROLE_TESTCENTER_MANAGER,
                    RestTestCenterUsers::class
                )
            );

            $this->setVersion('4.5.0');
        }

        $this->skip('4.5.0', '4.6.0');

        if ($this->isVersion('4.6.0')) {
            AclProxy::applyRule(
                new AccessRule(
                    'grant',
                    TestCenterService::ROLE_TESTCENTER_MANAGER,
                    RestEligibilities::class
                )
            );

            $this->setVersion('4.7.0');
        }

        $this->skip('4.7.0', '4.8.1');
    }
}
