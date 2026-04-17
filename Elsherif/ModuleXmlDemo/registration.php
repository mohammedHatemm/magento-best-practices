<?php
/**
 * Module registration file
 *
 * This file works together with etc/module.xml to register the module with Magento.
 * - registration.php: Tells Magento WHERE the module code is located
 * - module.xml: Tells Magento WHAT the module is (name, version, dependencies)
 */

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Elsherif_ModuleXmlDemo',
    __DIR__
);
