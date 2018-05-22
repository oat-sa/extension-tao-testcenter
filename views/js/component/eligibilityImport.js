/*
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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 *
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'module',
    'helpers',
    'uri',
    'ui/component',
    'tpl!taoTestCenter/component/eligibilityImport/layout',
    'ui/feedback',
    'ui/report',
    'layout/loading-bar',
    'async',
    'ui/modal',
    'css!taoTestCenterCss/eligibilityEditor',
    'ui/uploader'
], function($, _, __, module, helpers, uri, component, layoutTpl, feedback, reportFactory,loadingBar, async){
    'use strict';

    var _ns = '.eligibility-import';
    var _fileTypeFilters = ['text/csv', 'application/vnd.ms-excel'];
    var _fileExtFilter = /.+\.(csv)$/;
    var _modalDefaults = {
        width : 600
    };

    /**
     * Create an eligibility editor into a $container
     *
     * @param {JQuery} $container
     * @returns {Object} the eligibility editor instance
     */
    var eligibilityImportFactory = function eligibilityImportFactory(datatable, testCenterId) {

        var config = {
            uploadUrl :  helpers._url('import', 'TestCenterManager', 'taoTestCenter', {uri:testCenterId})
        };

        if(_.isEmpty(testCenterId)){
            throw new TypeError('The eligibility provider needs to be initialized with a test center');
        }
        var eligibilityImport;
        /**
         * Destroy the modal
         *
         * @param {object} instance
         * @returns {undefined}
         */
        var destroyModal = function destroyModal(){
            if(eligibilityImport && eligibilityImport.getElement()){
                datatable.trigger('reload');
                eligibilityImport.getElement()
                    .modal('destroy')
                    .remove();
            }
        };

        function initUpload() {
            // file uploader
            var errors = [];
            var $uploader = $('#upload-container');
            var $reportContainer = $('.report-container');

            $uploader.on('upload.uploader', function (e, file, data) {
                return reportFactory({
                    actions: [{
                        id: 'continue',
                        icon: 'right',
                        title: 'continue',
                        label: __('Continue')
                    }]
                }, data.report)
                    .on('action-continue', function() {
                        destroyModal();
                    }).render($reportContainer.find('.report'));
            }).on('fail.uploader', function (e, file, err) {
                errors.push(__('Unable to upload file %s : %s', file.name, err));
            }).on('end.uploader', function () {
                if (errors.length > 0) {
                    feedback().error(errorsToHtml(errors), {encodeHtml: false});
                }
                errors = [];
            });

            $uploader.uploader({
                upload: true,
                uploadUrl: config.uploadUrl,
                fileSelect : function fileSelect(files, done){
                    var givenLength = files.length;

                    //check the mime-type
                    files = _.filter(files, function(file){
                        // for some weird reasons some browsers have quotes around the file type
                        var checkType = file.type.replace(/("|')/g, '');
                        return _.contains(_fileTypeFilters, checkType) || (checkType === '' && _fileExtFilter.test(file.name));
                    });

                    if(files.length !== givenLength){
                        feedback().error('Invalid files have been removed');
                    }

                    async.filter(files, function(file, cb){cb(true);},done);
                },
            });
        }
        /**
         * Errors to unordered list
         *
         * @param {Array} errors
         * @returns {String}
         */
        function errorsToHtml(errors) {
            return '<ul><li>' + errors.join('</li><li>') + '</li></ul>';
        }
        /**
         * Add the editor into a popup and display it
         *
         * @param {Object} [modalConfig] - any config option available in ui/modal
         */
        var initModal = function initModal(modalConfig){
            modalConfig = _.defaults(modalConfig || {}, _modalDefaults);

            if(eligibilityImport && eligibilityImport.getElement()){
                eligibilityImport.getElement()
                    .addClass('modal')
                    .on('closed.modal', destroyModal)
                    .modal(modalConfig);
            }

            initUpload();
        };

        /**
         * The eligibilityImport API
         */
        var api = {

            /**
             * Add upload
             * @param {jQueryElement} $container - where to append the component
             * @param {Object} [options]
             * @param {String} [options.dataUrl] - to define where the tree data are retrieved
             * @returns {eligibilityImport} chains the component
             * @fires eligibilityImport#ok with the selected eligibities in parameter
             * @fires eligibilityImport#cancel
             */
            add : function add($container, options){
                return this.on('render', function(){
                    var self = this;
                    options = _.defaults(options || {}, this.config);

                    initModal({
                        width : 650
                    });

                    this.$component
                        .on('click' + _ns, '.actions .done', function(e){
                            e.preventDefault();
                            destroyModal();
                        }).on('click' + _ns, '.actions .cancel', function(e){
                            e.preventDefault();
                            destroyModal();
                            self.trigger('cancel');
                    });

                    this.trigger('open');
                })
                .init({
                    title :  __('Import Eligibilities from csv file.'),
                    editingMode : false
                })
                .render($container)
                .resize();
            },

            /**
             * Changes eligibilityImport modal height and adds scroll for the modal to fit the screen height
             *
             * @returns {eligibilityImport} chains the component
             */
            resize: function resize() {
                //40px is the default model top offset
                //it is set there to prevent situations in which it was already replaced by container scrollOffset value
                eligibilityImport.getElement().css({
                    'top': '40px',
                    'overflow-y': 'auto'
                });
                eligibilityImport.getElement().css('max-height', ($(window).height() - (parseInt(eligibilityImport.getElement().css('top'), 10) * 2)) + 'px');
                return this;
            }
        };

        //creates the component here
        eligibilityImport = component(api, config).setTemplate(layoutTpl);

        return eligibilityImport;
    };

    return eligibilityImportFactory;
});
