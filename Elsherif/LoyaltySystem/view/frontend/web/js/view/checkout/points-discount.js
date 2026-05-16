/**
 * Loyalty Points Knockout Component
 */
define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Ui/js/model/messageList',
    'mage/translate'
], function (
    ko,
    $,
    Component,
    quote,
    totals,
    storage,
    urlBuilder,
    errorProcessor,
    messageList,
    $t
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Elsherif_LoyaltySystem/checkout/points-form'
        },

        pointsToUse: ko.observable(0),
        availablePoints: ko.observable(0),
        isLoading: ko.observable(false),
        isApplied: ko.observable(false),
        maxPointsAllowed: ko.observable(0),

        initialize: function () {
            this._super();
            this.loadCustomerPoints();

            quote.totals.subscribe(function (totals) {
                this.updateMaxPoints(totals);
            }, this);

            return this;
        },

        loadCustomerPoints: function () {
            var self = this;

            if (!window.isCustomerLoggedIn) {
                return;
            }

            var serviceUrl = urlBuilder.createUrl('/loyalty/balance/:customerId', {
                customerId: window.customerData.id
            });

            storage.get(serviceUrl)
                .done(function (response) {
                    self.availablePoints(response.points);
                    self.updateMaxPoints(quote.totals());
                })
                .fail(function (response) {
                    console.error('Failed to load points:', response);
                });
        },

        updateMaxPoints: function (totals) {
            var redeemRate = 10; // From config
            var maxByCart = Math.floor(totals.grand_total * redeemRate);
            var maxByBalance = this.availablePoints();

            this.maxPointsAllowed(Math.min(maxByCart, maxByBalance));
        },

        applyPoints: function () {
            var self = this;
            var points = parseInt(this.pointsToUse());

            if (!this.validatePoints(points)) {
                return;
            }

            this.isLoading(true);

            var payload = {
                quoteId: quote.getQuoteId(),
                points: points
            };

            storage.post(
                urlBuilder.createUrl('/loyalty/redeem', {}),
                JSON.stringify(payload)
            ).done(function (response) {
                if (response.success) {
                    self.isApplied(true);

                    // Reload totals
                    var deferred = $.Deferred();
                    totals.isLoading(true);

                    storage.get(
                        urlBuilder.createUrl('/carts/mine/totals', {})
                    ).done(function (data) {
                        quote.setTotals(data);
                        deferred.resolve();
                    }).always(function () {
                        totals.isLoading(false);
                    });

                    messageList.addSuccessMessage({
                        message: $t('Points applied successfully!')
                    });
                }
            }).fail(function (response) {
                errorProcessor.process(response, messageList);
            }).always(function () {
                self.isLoading(false);
            });
        },

        cancelPoints: function () {
            var self = this;
            this.isLoading(true);

            storage.post(
                urlBuilder.createUrl('/loyalty/cancel', {}),
                JSON.stringify({ quoteId: quote.getQuoteId() })
            ).done(function () {
                self.isApplied(false);
                self.pointsToUse(0);

                // Reload totals
                totals.isLoading(true);
                storage.get(
                    urlBuilder.createUrl('/carts/mine/totals', {})
                ).done(function (data) {
                    quote.setTotals(data);
                }).always(function () {
                    totals.isLoading(false);
                });
            }).always(function () {
                self.isLoading(false);
            });
        },

        validatePoints: function (points) {
            if (!points || points <= 0) {
                messageList.addErrorMessage({
                    message: $t('Please enter a valid number of points.')
                });
                return false;
            }

            if (points > this.maxPointsAllowed()) {
                messageList.addErrorMessage({
                    message: $t('You can use maximum %1 points.').replace('%1', this.maxPointsAllowed())
                });
                return false;
            }

            return true;
        },

        getDiscountPreview: function () {
            var points = parseInt(this.pointsToUse()) || 0;
            return (points / 10).toFixed(2); // Redemption rate = 10
        }
    });
});
