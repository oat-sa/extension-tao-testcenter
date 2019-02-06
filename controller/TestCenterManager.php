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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoTestCenter\controller;

use oat\generis\model\data\event\ResourceUpdated;
use oat\oatbox\event\EventManager;
use oat\tao\model\import\service\ImportMapperInterface;
use oat\tao\model\import\service\RdsValidatorValueMapper;
use oat\tao\model\resources\ResourceWatcher;
use oat\tao\model\Tree\GetTreeRequest;
use oat\tao\model\Tree\GetTreeService;
use oat\taoDevTools\actions\ExtensionsManager;
use oat\taoTestCenter\model\gui\form\TreeFormFactory;
use oat\taoTestCenter\model\import\EligibilityCsvImporterFactory;
use oat\taoTestCenter\model\TestCenterService;
use oat\taoTestCenter\model\EligibilityService;
use oat\taoProctoring\helpers\DataTableHelper;
use oat\taoProctoring\model\textConverter\ProctoringTextConverterTrait;

/**
 * Proctoring Test Center controllers for test center screens
 *
 * @author Open Assessment Technologies SA
 * @package oat\taoTestCenter\controller
 * @license GPL-2.0
 *
 */
class TestCenterManager extends \tao_actions_SaSModule
{
    use ProctoringTextConverterTrait;

    /**
     * Initialize the service and the default data
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = $this->getClassService();
    }

    /**
     * Edit a Test Center instance
     *
     * @throws \tao_models_classes_MissingRequestParameterException
     * @throws \tao_models_classes_dataBinding_GenerisFormDataBindingException
     *
     * @requiresRight uri READ
     */
    public function editCenter()
    {
        $clazz = $this->getCurrentClass();
        $testCenter = $this->getCurrentInstance();

        $formContainer = new \tao_actions_form_Instance($clazz, $testCenter);
        $myForm = $formContainer->getForm();
        if ($myForm->isSubmited()) {
            if ($myForm->isValid()) {

                $binder = new \tao_models_classes_dataBinding_GenerisFormDataBinder($testCenter);
                $testCenter = $binder->bind($myForm->getValues());

                $this->setData("selectNode", \tao_helpers_Uri::encode($testCenter->getUri()));
                $this->setData('message', $this->convert('Test center saved'));
                $this->setData('reload', true);
            }
        }

        $forms = $this->getServiceLocator()->get(TreeFormFactory::SERVICE_ID)->renderForms($testCenter);
        $this->setData('forms', $forms);

        $updatedAt = $this->getServiceLocator()->get(ResourceWatcher::SERVICE_ID)->getUpdatedAt($testCenter);
        $this->setData('updatedAt', $updatedAt);
        $this->setData('formTitle', $this->convert('Edit test center'));
        $this->setData('testCenter', $testCenter->getUri());
        $this->setData('myForm', $myForm->render());
        $this->setView('TestCenterManager/editCenter.tpl');
    }

    /**
     * @throws \Exception
     */
    public function import()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            throw new \Exception('Only post method allowed');
        }
        $files = (array) $_FILES;

        $testCenter = $this->getCurrentInstance();
        /** @var EligibilityCsvImporterFactory $service */
        $service = $this->getServiceLocator()->get(EligibilityCsvImporterFactory::SERVICE_ID);
        $propertyKey = $this->getImportMapperTestCenterProperty();

        if (empty($files)){
            throw new \Exception('No files selected.');
        }

        foreach ($files as $file){
            $report = $service->create('default')->import($file['tmp_name'],[
                $propertyKey => $testCenter
            ]);
        }

        $data['report'] = $report;
        $this->returnJson($data);
    }

    /**
     * Get eligibilities formatted in a way that is compatible
     * with the eligibilityTable component
     *
     * @return array
     * @throws \tao_models_classes_MissingRequestParameterException
     */
    private function _getEligibilities(){

        $testCenter = $this->getCurrentInstance();

        $data = array_map(function($eligibility) {
            $eligibility['id'] = $eligibility['uri'];
            return $eligibility;
        }, $this->getEligibilityService()->getEligibilities($testCenter, [ 'sort' => true ]));

        return DataTableHelper::paginate($data, $this->getRequestOptions());
    }

    /**
     * this one was moved out of generis tree controller into here to allow to fetch tree data altogether with permissions
     * used only if DACSimple extension is enabled
     *
     * @throws \common_Exception
     * @throws \common_exception_IsAjaxAction
     */
    public function getData()
    {
        /** @var GetTreeService $service */
        $service = $this->getServiceLocator()->get(GetTreeService::SERVICE_ID);

        $response = $service->handle(GetTreeRequest::create($this->getRequest()));

        $tree = $response->getTreeArray();

        /** @var \common_ext_ExtensionsManager $extMgr */
        $extMgr = $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID);
        $isDacEnabled = $extMgr->isInstalled('taoDacSimple') && $extMgr->isEnabled('taoDacSimple');

        if ($isDacEnabled) {

            //retrieve resources permissions
            $user = \common_Session_SessionManager::getSession()->getUser();
            $permissions = $this->getResourceService()->getResourcesPermissions($user, $tree);

            $data = [
                'tree' => $tree,
                'permissions' => $permissions
            ];
        } else {
            $data = $tree;
        }
        return $this->returnJson($data);
    }

    /**
     * Get the list of eligibilities.
     *
     * Reformat them for compat.
     *
     * @return array
     * @throws \common_Exception
     */
    private function _getRequestEligibility(){
        if($this->hasRequestParameter('eligibility')){
            $eligibility = $this->getRequestParameter('eligibility');
            if(isset($eligibility['deliveries']) && is_array($eligibility['deliveries'])){

                $formatted = array();
                $formatted['deliveries'] = array_map(function($deliveryUri){
                        return new \core_kernel_classes_Resource(\tao_helpers_Uri::decode($deliveryUri));
                    }, $eligibility['deliveries']);

                if(isset($eligibility['testTakers']) && is_array($eligibility['testTakers'])){
                    $formatted['testTakers'] = array_map(function($testTakerId){
                        return \tao_helpers_Uri::decode($testTakerId);
                    }, $eligibility['testTakers']);
                }

                return $formatted;
            }else{
                throw new \common_Exception('eligibility requires a delivery');
            }
        }else{
            throw new \common_Exception('no eligibility in request');
        }
    }

    public function getEligibilities()
    {
        return $this->returnJson($this->_getEligibilities());
    }

    public function addEligibilities()
    {
        $testCenter = $this->getCurrentInstance();
        $eligibility = $this->_getRequestEligibility();
        $failures = array();
        $success = true;
        foreach($eligibility['deliveries'] as $delivery){
            if($delivery->isClass()){
                continue;//prevent assigning eligibility to a class for now
            }
            if($this->getEligibilityService()->createEligibility($testCenter, $delivery)){
                if(isset($eligibility['testTakers'])){
                    $success &= $this->getEligibilityService()->setEligibleTestTakers($testCenter, $delivery, $eligibility['testTakers']);
                }
            }else{
                $success = false;
                $failures[] = $delivery->getLabel();
            }
        }

        // Trigger ResourceUpdated event for updating updatedAt field for resource
        $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);
        $eventManager->trigger(new ResourceUpdated($testCenter));

        return $this->returnJson(array(
            'success' => $success,
            'failed' => $failures
        ));
    }

    public function editEligibilities()
    {
        $success = false;
        $testCenter = $this->getCurrentInstance();
        $eligibility = $this->_getRequestEligibility();
        if(isset($eligibility['testTakers'])){
            foreach($eligibility['deliveries'] as $delivery){
                $success = $this->getEligibilityService()->setEligibleTestTakers($testCenter, $delivery, $eligibility['testTakers']);
            }
            // Trigger ResourceUpdated event for updating updatedAt field for resource
            $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);
            $eventManager->trigger(new ResourceUpdated($testCenter));
        }else{
            //nothing to save, so consider it done
            $success = true;
        }
        return $this->returnJson(array(
            'success' => $success
        ));
    }

    /**
     * delete an instance or a class
     * called via ajax
     */
    public function delete()
    {
        $deleted = $this->getClassService()->deleteResource($this->getCurrentInstance());
        return $this->returnJson(array(
            'deleted' => $deleted
        ));
    }

    /**
     * Remove the eligibility in parameter
     * @throws \common_Exception without an eligibility
     */
    public function removeEligibilities()
    {
        $testCenter = $this->getCurrentInstance();
        $eligibility = $this->_getRequestEligibility();
        $success = true;
        foreach ($eligibility['deliveries'] as $delivery) {
            $success = $success && $this->getEligibilityService()->removeEligibility($testCenter, $delivery);
        }
        // Trigger ResourceUpdated event for updating updatedAt field for resource
        $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);
        $eventManager->trigger(new ResourceUpdated($testCenter));
        return $this->returnJson(array(
            'success' => $success
        ));
    }

    /**
     * Change the eligibility in parameter to use the proctored authorization (shield)
     * @throws \common_Exception without an eligibility
     */
    public function shieldEligibility()
    {
        if(!$this->hasRequestParameter('eligibility')){
            throw new \common_Exception('Please provide the URI of the eligibilty to shield');
        }
        $eligibilityUri = $this->getRequestParameter('eligibility');

        $this->getEligibilityService()->setByPassProctor(new \core_kernel_classes_Resource($eligibilityUri), false);
        return $this->returnJson(array(
            'success' => true
        ));
    }

    /**
     * Change the eligibility in parameter to use the default authorization (unshield)
     * @throws \common_Exception without an eligibility
     */
    public function unshieldEligibility()
    {
        if(!$this->hasRequestParameter('eligibility')){
            throw new \common_Exception('Please provide the URI of the eligibilty to unshield');
        }
        $eligibilityUri = $this->getRequestParameter('eligibility');

        $this->getEligibilityService()->setByPassProctor(new \core_kernel_classes_Resource($eligibilityUri), true);
        return $this->returnJson(array(
            'success' => true
        ));
    }

    /**
     * Gets the data table request options
     *
     * @return array
     */
    protected function getRequestOptions() {

        $page = $this->hasRequestParameter('page') ? $this->getRequestParameter('page') : DataTableHelper::DEFAULT_PAGE;
        $rows = $this->hasRequestParameter('rows') ? $this->getRequestParameter('rows') : DataTableHelper::DEFAULT_ROWS;
        $sortBy = $this->hasRequestParameter('sortby') ? $this->getRequestParameter('sortby') : 'Delivery';
        $sortOrder = $this->hasRequestParameter('sortorder') ? $this->getRequestParameter('sortorder') : 'asc';
        $filter = $this->hasRequestParameter('filter') ? $this->getRequestParameter('filter') : null;

        return array(
            'page' => $page,
            'rows' => $rows,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'filter' => $filter
        );

    }

    /**
     * @throws \common_exception_NotFound
     * @throws \oat\oatbox\service\exception\InvalidService
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     * @return string
     * @throws \Exception
     */
    protected function getImportMapperTestCenterProperty()
    {
        /** @var EligibilityCsvImporterFactory $service */
        $service = $this->getServiceLocator()->get(EligibilityCsvImporterFactory::SERVICE_ID);
        $mapper  = $service->create('default')->getMapper();

        $schema = $mapper->getOption(ImportMapperInterface::OPTION_SCHEMA);
        if(!isset($schema[ImportMapperInterface::OPTION_SCHEMA_MANDATORY])){
            return null;
        }

        $mandatoryFields = $schema[ImportMapperInterface::OPTION_SCHEMA_MANDATORY];
        foreach ($mandatoryFields as $key => $propertyKey) {
            $class = null;
            if (is_array($propertyKey) && count($propertyKey) === 1){
                $valueMapper = reset($propertyKey);
                if ($valueMapper instanceof RdsValidatorValueMapper){
                    $class = $valueMapper->getOption(RdsValidatorValueMapper::OPTION_CLASS);
                }
            } else {
                $class = $propertyKey;
            }

            if (TestCenterService::CLASS_URI === $class){
                return $key;
            }
        }

        throw new \Exception('Class uri: ' . TestCenterService::CLASS_URI .' is not defined in the import mapper config.');
    }

    /**
     * @return TestCenterService
     */
    protected function getClassService()
    {
        return TestCenterService::singleton();
    }

    /**
     * @return EligibilityService
     */
    protected function getEligibilityService()
    {
        return $this->getServiceLocator()->get(EligibilityService::SERVICE_ID);
    }

    /**
     * overwrite the parent addInstance to add the requiresRight only in TestTakers
     * @requiresRight id WRITE
     */
    public function addInstance()
    {
        parent::addInstance();
    }

    /**
     * overwrite the parent addSubClass to add the requiresRight only in TestTakers
     * @requiresRight id WRITE
     */
    public function addSubClass()
    {
        parent::addSubClass();
    }

    /**
     * overwrite the parent cloneInstance to add the requiresRight only in TestTakers
     * @see tao_actions_TaoModule::cloneInstance()
     * @requiresRight uri READ
     * @requiresRight classUri WRITE
     */
    public function cloneInstance()
    {
        return parent::cloneInstance();
    }
}
