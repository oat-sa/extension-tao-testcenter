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
use oat\generis\model\OntologyRdf;
use oat\tao\model\upload\UploadService;
use oat\taoTestCenter\controller\form\CsvImporter;
use oat\taoTestCenter\model\import\TestCenterCsvImporterFactory;
use tao_actions_form_Import;

/**
 * Extends the common Import class to exchange the generic
 * CsvImporter with a subject specific one
 *
 *
 */
class Import extends \tao_actions_Import
{
    /**
     * @throws \common_Exception
     * @throws \common_exception_NotFound
     * @throws \oat\oatbox\service\exception\InvalidService
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function index(){

        $importer = $this->getCurrentImporter();
        $formContainer = new tao_actions_form_Import(
            $importer,
            $this->getAvailableImportHandlers(),
            $this->getCurrentClass()
        );
        $myForm = $formContainer->getForm();

        //if the form is submited and valid
        if($myForm->isSubmited()){
            if($myForm->isValid()){
                $options = $myForm->getValues();

                /** @var UploadService $uploadService */
                $uploadService = $this->getServiceLocator()->get(UploadService::SERVICE_ID);
                $file = $uploadService->getUploadedFlyFile($options['importFile']);

                /** @var TestCenterCsvImporterFactory $testCenterImport */
                $testCenterImport = $this->getServiceLocator()->get(TestCenterCsvImporterFactory::SERVICE_ID);
                $importerService = $testCenterImport->create('default');

                $report = $importerService->import($file,[
                    OntologyRdf::RDF_TYPE => $options['classUri']
                ], [
                    'delimiter' => $options['field_delimiter'],
                    'enclosure' => $options['field_encloser'],
                ]);

                return $this->returnReport($report);
            }
        }

        $this->setData('myForm', $myForm->render());
        $this->setData('formTitle', __('Import '));
        $this->setView('form/import.tpl', 'tao');
    }

    /**
     * (non-PHPdoc)
     * @see tao_actions_Import::getAvailableImportHandlers()
     */
    public function getAvailableImportHandlers()
    {
        $returnValue = [
            new CsvImporter()
        ];

        return $returnValue;
    }

}
