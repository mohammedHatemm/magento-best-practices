/**
 * Elsherif Loyalty System - RequireJS Configuration
 */
var config = {
    map: {
        '*': {
            'loyaltyPoints': 'Elsherif_LoyaltySystem/js/view/checkout/points-discount'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/summary/abstract-total': {
                'Elsherif_LoyaltySystem/js/view/summary/loyalty-mixin': true
            }
        }
    }
};
