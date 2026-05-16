/**
 * Loyalty Points Mixin for Checkout Summary
 * Extends abstract-total to display loyalty discount
 */
define([
    'mage/utils/wrapper'
], function (wrapper) {
    'use strict';

    return function (Component) {
        return Component.extend({
            /**
             * Get loyalty discount value from totals
             */
            getLoyaltyDiscount: function () {
                var totals = this.totals();
                if (totals && totals.extension_attributes) {
                    return totals.extension_attributes.loyalty_discount_amount || 0;
                }
                return 0;
            },

            /**
             * Check if loyalty discount is applied
             */
            isLoyaltyApplied: function () {
                return this.getLoyaltyDiscount() > 0;
            }
        });
    };
});
