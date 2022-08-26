/*
 * Copyright © 2022 TechNWeb, Inc. All rights reserved.
 * See TNW_LICENSE.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, Modal, alert, $t) {
    'use strict';

    return Modal.extend({
        defaults: {
            imports: {
                logAction:  '${ $.provider }:data.logAction'
            }
        },

        /** @inheritdoc */
        initObservable: function () {
            return this._super()
                .observe([
                    'responseData',
                    'responseStatus'
                ]);
        },

        /**
         * Stop
         */
        stopVideo: function() {
            var iframe = $('.-quick-survey-video-wrapper iframe');
            if (iframe.length > 0) {
                iframe[0].contentWindow.postMessage('{"event":"command","func":"stopVideo","args":""}', '*');
            }
        },

        closeSnoozeSurvey: function () {
            this.source.set('data.snooze_survey', true);
            this.closeModal();
        },

        /**
         * @inheritDoc
         */
        closeModal: function() {
            var _super = this._super.bind(this);

            this.stopVideo();

            this.source.save({
                ajaxSave: true,
                ajaxSaveType: 'default',
                response: {
                    data: function (data) {
                        if (data.success) {
                            _super();
                            return;
                        }

                        alert({
                            content: data.error_message || $t('An error occurred while logging process.')
                        })
                    },
                    status: this.responseStatus
                },
                attributes: {
                    id: this.namespace
                }
            });
        },

        /**
         * Close release notes
         */
        closeReleaseSurvey: function () {
            this.actionDone();
        }
    });
});
