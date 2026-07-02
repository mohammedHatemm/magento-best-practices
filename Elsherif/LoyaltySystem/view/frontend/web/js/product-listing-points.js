/**
 * Loyalty Points for Product Listing
 */
define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    return function (config) {
        var products = config.products || {};
        var template = config.template || '';

        /**
         * Add points badge to a product item
         */
        function addPointsBadge($item, points) {
            if (points <= 0) return;
            if ($item.find('.loyalty-points-badge-listing').length > 0) return;

            var html = template.replace('{{points}}', points);
            
            // Find the best place to insert
            var $target = $item.find('.price-box').first();
            if ($target.length) {
                $target.after(html);
                return;
            }
            
            $target = $item.find('.product-item-details').first();
            if ($target.length) {
                $target.find('.product-item-name').after(html);
            }
        }

        /**
         * Find product ID from item element
         */
        function findProductId($item) {
            var productId = null;

            // Method 1: data-product-id
            productId = $item.data('product-id');
            if (productId) return productId;

            // Method 2: Find in child elements
            var $withId = $item.find('[data-product-id]').first();
            if ($withId.length) {
                return $withId.data('product-id');
            }

            // Method 3: From form action (add to cart)
            var $form = $item.find('form[action*="checkout/cart/add"]');
            if ($form.length) {
                var action = $form.attr('action');
                var match = action.match(/product\/(\d+)/);
                if (match) return parseInt(match[1]);
            }

            // Method 4: From wishlist form
            $form = $item.find('form[data-action="add-to-wishlist"]');
            if ($form.length) {
                var dataPost = $form.attr('data-post');
                if (dataPost) {
                    try {
                        var postData = JSON.parse(dataPost);
                        if (postData.data && postData.data.product) {
                            return parseInt(postData.data.product);
                        }
                    } catch (e) {}
                }
            }

            // Method 5: From compare link
            var $compare = $item.find('a[data-post*="product"]');
            if ($compare.length) {
                var compareData = $compare.attr('data-post');
                if (compareData) {
                    try {
                        var cData = JSON.parse(compareData);
                        if (cData.data && cData.data.product) {
                            return parseInt(cData.data.product);
                        }
                    } catch (e) {}
                }
            }

            // Method 6: Match by URL
            var $link = $item.find('a.product-item-link, a.product-item-photo').first();
            if ($link.length) {
                var href = $link.attr('href');
                if (href) {
                    for (var id in products) {
                        if (products[id].url_key && href.indexOf(products[id].url_key) !== -1) {
                            return parseInt(id);
                        }
                    }
                }
            }

            return null;
        }

        // Process all product items
        $('.product-item').each(function () {
            var $item = $(this);
            var productId = findProductId($item);

            if (productId && products[productId]) {
                addPointsBadge($item, products[productId].points);
            }
        });

        // Also handle widget products (like homepage widgets)
        $('.product-item-info').each(function () {
            var $item = $(this).closest('.product-item');
            if ($item.length === 0) {
                $item = $(this);
            }
            
            var productId = findProductId($item);
            if (!productId) {
                productId = findProductId($(this));
            }

            if (productId && products[productId]) {
                addPointsBadge($item, products[productId].points);
            }
        });
    };
});
