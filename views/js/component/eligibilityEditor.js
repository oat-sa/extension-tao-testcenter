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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
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
    'generis.tree.select',
    'tpl!taoTestCenter/component/eligibilityEditor/layout',
    'ui/feedback',
    'ui/modal',
    'css!taoTestCenterCss/eligibilityEditor'
], function($, _, __, module, helpers, uri, component, GenerisTreeSelectClass, layoutTpl, feedback){
    'use strict';

    var _ns = '.eligibility-editor';

    var _modalDefaults = {
        width : 600
    };

    var config = {
        dataUrl :  helpers._url('getData', 'GenerisTree', 'tao'),
        deliveriesOrder : 'http://www.w3.org/2000/01/rdf-schema#label',
        deliveriesOrderdir : 'asc',
        isDacEnabled: false,
        testTakerDataUrl :  helpers._url('getData', 'TestCenterManager', 'taoTestCenter')
    };

    config = _.defaults({}, module.config(), config);

    if (_.isObject(config.testTakerDataUrl)) {
        config.testTakerDataUrl = helpers._url(config.testTakerDataUrl.action, config.testTakerDataUrl.controller, config.testTakerDataUrl.extension);
    } else {
        config.testTakerDataUrl = config.dataUrl;
    }

    /**
     * Create an eligibility editor into a $container
     *
     * @param {JQuery} $container
     * @param {Array} eligibilities
     * @param {Object} [delivery]
     * @param {String} [delivery.label]
     * @param {String} [delivery.uri]
     * @returns {Object} the eligibility editor instance
     */
    var eligibilityEditorFactory = function eligibilityEditorFactory() {

        var eligibilityEditor;

        var testTakerTreeId = _.uniqueId('eligible-testTaker-tree-');//generating the generis tree id, because it requires one to work
        var deliveryTreeId  = _.uniqueId('eligible-delivery-tree-');//generating the generis tree id, because it requires one to work

        /**
         * Builds a tree to select test takers
         *
         * @param {String} id - the tree identifier, use to get the DOM node to put the tree
         * @param {String} url - the tree data url
         * @param {Array} [testTakers] - array of currently selected test takers
         * @returns {tree} the created tree
         */
        var buildTestTakerTree = function buildTestTakerTree(id, url, testTakers){

            var selected = _.pluck(testTakers, 'uri');

            return new GenerisTreeSelectClass('#' + id, url, {
                actionId : 'treeOptions.actionId',
                saveUrl : 'treeOptions.saveUrl',
                saveData : {},
                checkedNodes : _.map(selected, uri.encode), //generis tree uses "encoded uri" to check nodes
                serverParameters : {
                    openParentNodes : selected, //generis tree uses normal if to open nodes...
                    rootNode : 'http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject'
                },
                paginate : 10,
                checkResourcePermissions: config.isDacEnabled
            });
        };

        /**
         * Builds a tree to select deliveries
         *
         * @param {String} id - the tree identifier, use to get the DOM node to put the tree
         * @param {String} url - the tree data url
         * @param {Array} [deliveries] - array of currently selected deliveris
         * @returns {tree} the created tree
         */
        var buildDeliveryTree = function buildDeliveryTree(id, url, deliveries){

            var selected = _.pluck(deliveries, 'uri');
            return new GenerisTreeSelectClass('#' + id, url, {
                actionId : 'treeOptions.actionId',
                saveUrl : 'treeOptions.saveUrl',
                saveData : {},
                checkedNodes : _.map(selected, uri.encode), //generis tree uses "encoded uri" to check nodes
                serverParameters : {
                    openParentNodes : selected, //generis tree uses normal if to open nodes...
                    rootNode : 'http://www.tao.lu/Ontologies/TAODelivery.rdf#AssembledDelivery',
                    order : config.deliveriesOrder,
                    orderdir : config.deliveriesOrderdir
                },
                paginate : 10
            });
        };

        /**
         * Destroy the modal
         *
         * @param {object} instance
         * @returns {undefined}
         */
        var destroyModal = function destroyModal(){
            if(eligibilityEditor && eligibilityEditor.getElement()){
                eligibilityEditor.getElement()
                    .modal('destroy')
                    .remove();
            }
        };

        /**
         * Add the editor into a popup and display it
         *
         * @param {Object} [modalConfig] - any config option available in ui/modal
         */
        var initModal = function initModal(modalConfig){
            modalConfig = _.defaults(modalConfig || {}, _modalDefaults);

            if(eligibilityEditor && eligibilityEditor.getElement()){
                eligibilityEditor.getElement()
                    .addClass('modal')
                    .on('closed.modal', destroyModal)
                    .modal(modalConfig);
            }
        };

        /**
         * The eligibiltiyEditor API
         */
        var api = {

            /**
             * Add eligibilities
             * @param {jQueryElement} $container - where to append the component
             * @param {Object} [options]
             * @param {String} [options.dataUrl] - to define where the tree data are retrieved
             * @returns {eligibilityEditor} chains the component
             * @fires eligibilityEditor#ok with the selected eligibities in parameter
             * @fires eligibilityEditor#cancel
             */
            add : function add($container, options){
                return this.on('render', function(){
                    var self = this;
                    var deliveryTree;
                    var testTakerTree;

                    options = _.defaults(options || {}, this.config);

                    deliveryTree = buildDeliveryTree(deliveryTreeId, options.dataUrl);
                    testTakerTree = buildTestTakerTree(testTakerTreeId, options.testTakerDataUrl);

                    initModal({
                        width : 650
                    });

                    this.$component
                        .on('click' + _ns, '.actions .done', function(e){

                            var deliveries = _(deliveryTree.getChecked()).uniq().compact().value();
                            var testTakers = _(testTakerTree.getChecked()).uniq().compact().value();

                            if( deliveries && deliveries.length){
                                self.trigger('ok', {
                                    deliveries: deliveries,
                                    testTakers: testTakers
                                });
                                destroyModal();
                            } else {
                                feedback(self.$component).warning(__('At least one delivery need to be selected to create'));
                            }

                        }).on('click' + _ns, '.actions .cancel', function(e){
                            e.preventDefault();
                            destroyModal();
                            self.trigger('cancel');
                        });
                   this.trigger('open');
                })
                .init({
                    title :  __('Add Eligibility'),
                    editingMode : false,
                    subjectTreeId : testTakerTreeId,
                    deliveryTreeId : deliveryTreeId,
                })
                .render($container)
                .resize();

            },

            /**
             * Add eligibilities
             * @param {jQueryElement} $container - where to append the component
             * @param {String} deliveryName - the name of the eligibility's deliveryA
             * @param {Array} testTakers - the test takers already selected
             * @param {Object} [options]
             * @param {String} [options.dataUrl] - to define where the tree data are retrieved
             * @returns {eligibilityEditor} chains the component
             * @fires eligibilityEditor#ok with the selected test takers in parameter
             * @fires eligibilityEditor#cancel
             */
            edit : function edit($container, deliveryName, testTakers, options){
                return this.on('render', function(){
                    var self = this;
                    var testTakerTree;

                    options = _.defaults(options || {}, this.config);

                    testTakerTree = buildTestTakerTree(testTakerTreeId, options.testTakerDataUrl, testTakers);

                    initModal({
                        width : 400
                    });

                    this.$component
                        .on('click' + _ns, '.actions .done', function(e){

                            var testTakers = _(testTakerTree.getChecked()).uniq().compact().value();
                            self.trigger('ok', {
                                testTakers : testTakers
                            });
                            destroyModal();

                        }).on('click' + _ns, '.actions .cancel', function(e){
                            e.preventDefault();
                            destroyModal();
                            self.trigger('cancel');
                        });

                   this.trigger('open');
                })
                .init({
                    title :  __('Edit Eligibility'),
                    editingMode : true,
                    subjectTreeId : testTakerTreeId,
                    deliveryTreeId : deliveryTreeId,
                    deliveryName : deliveryName
                })
                .render($container)
                .resize();
            },

            /**
             * Changes eligibilityEditor modal height and adds scroll for the modal to fit the screen height
             *
             * @returns {eligibilityEditor} chains the component
             */
            resize: function resize() {
                //40px is the default model top offset
                //it is set there to prevent situations in which it was already replaced by container scrollOffset value
                eligibilityEditor.getElement().css({
                    'top': '40px',
                    'overflow-y': 'auto'
                });
                eligibilityEditor.getElement().css('max-height', ($(window).height() - (parseInt(eligibilityEditor.getElement().css('top'), 10) * 2)) + 'px');
                return this;
            }
        };

        //creates the component here
        eligibilityEditor = component(api, config).setTemplate(layoutTpl);

        return eligibilityEditor;
    };

    return eligibilityEditorFactory;
});
