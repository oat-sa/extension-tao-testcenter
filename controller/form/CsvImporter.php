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

namespace oat\taoTestCenter\controller\form;

use http\Exception\InvalidArgumentException;
use tao_helpers_form_FormElement;
use tao_models_classes_import_CsvImporter;
use tao_models_classes_import_CSVMappingForm;
use tao_models_classes_import_CsvUploadForm;

class CsvImporter extends tao_models_classes_import_CsvImporter
{
    protected $defaultOptions = [
        tao_models_classes_import_CsvUploadForm::IS_OPTION_FIRST_COLUMN_ENABLE => false
    ];

    /**
     * (non-PHPdoc)
     * @see tao_models_classes_import_ImportHandler::getForm()
     */
    public function getForm()
    {
        $form = empty($_POST['source']) && empty($_POST['importFile'])
            ? new tao_models_classes_import_CsvUploadForm([], $this->defaultOptions)
            : $this->createFormContainer();
        return $form->getForm();
    }

    /**
     * @return tao_models_classes_import_CSVMappingForm
     */
    protected function createFormContainer()
    {
        $sourceContainer = new tao_models_classes_import_CsvUploadForm();
        $sourceForm = $sourceContainer->getForm();

        /** @var tao_helpers_form_FormElement $element */
        foreach ($sourceForm->getElements() as $element) {
            $element->feed();
        }

        $sourceForm->getElement('source')->feed();
        $fileInfo = $sourceForm->getValue('source');

        if (isset($_POST['importFile'])) {
            $serial = $_POST['importFile'];
        } else {
            $serial = $fileInfo['uploaded_file'];
        }

        if (!is_string($serial)) {
            throw new InvalidArgumentException('Import file has to be a valid file serial.');
        }

        $values = $sourceForm->getValues();
        $values['importFile'] = $serial;

        $myFormContainer = new tao_models_classes_import_CSVMappingForm($values, array_merge([
            'class_properties' => [],
            'ranged_properties' => [],
            'csv_column' => [],
        ],$this->defaultOptions));

        return $myFormContainer;
    }
}