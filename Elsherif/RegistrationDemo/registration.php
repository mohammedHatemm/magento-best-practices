<?php
declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

// MODULE
ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Elsherif_RegistrationDemo',
    __DIR__
);

// THEME
//ComponentRegistrar::register(
//    ComponentRegistrar::THEME,
//    'frontend/Elsherif/theme-name',  // للثيمات يكون الاسم مختلف
//    __DIR__
//);

// LIBRARY
//ComponentRegistrar::register(
//    ComponentRegistrar::LIBRARY,
//    'Elsherif_RegistrationDemo',
//    __DIR__
//);

// LANGUAGE
//ComponentRegistrar::register(
//    ComponentRegistrar::LANGUAGE,
//    'elsherif_ar_eg',
//    __DIR__
//);
