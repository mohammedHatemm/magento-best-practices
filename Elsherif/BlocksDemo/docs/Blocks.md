# 🧱 Blocks
## Complete Guide to Blocks in Magento 2

---

## 📑 Table of Contents
1. [Introduction](#1-introduction)
2. [File Location](#2-file-location)
3. [Block Types](#3-block-types)
4. [Template Block](#4-template-block)
5. [Connecting Block to Layout](#5-connecting-block-to-layout)
6. [Block Methods](#6-block-methods)
7. [Caching](#7-caching)
8. [ViewModels](#8-viewmodels)
9. [Best Practices](#9-best-practices)
10. [Core Classes & Paths](#10-core-classes--paths)
11. [Block Lifecycle](#11-block-lifecycle)
12. [Data Arguments](#12-data-arguments)
13. [Common Mistakes](#13-common-mistakes)
14. [Debugging Tips](#14-debugging-tips)

---

## 1. Introduction

### What is a Block?
A Block is the bridge between **Business Logic** and the **Template (View)**.

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  Controller  │────▶│    Layout    │────▶│    Block     │
└──────────────┘     └──────────────┘     └──────┬───────┘
                                                 │
                                                 ▼
                                          ┌──────────────┐
                                          │ Model/Service│
                                          └──────┬───────┘
                                                 │
                                                 ▼
                                          ┌──────────────┐
                                          │   Template   │
                                          │   (.phtml)   │
                                          └──────┬───────┘
                                                 │
                                                 ▼
                                          ┌──────────────┐
                                          │ HTML Output  │
                                          └──────────────┘
```

---

## 2. File Location

```
app/code/Vendor/Module/
├── Block/
│   ├── SomeBlock.php
│   └── Adminhtml/
│       └── Entity/
│           └── Edit.php
└── view/
    └── frontend/
        └── templates/
            └── some_block.phtml
```

---

## 3. Block Types

| Type | Base Class | Usage |
|------|------------|-------|
| Template | `Magento\Framework\View\Element\Template` | With .phtml template |
| AbstractBlock | `Magento\Framework\View\Element\AbstractBlock` | Without template |
| Admin | `Magento\Backend\Block\Template` | Admin area |
| Text | `Magento\Framework\View\Element\Text` | Raw text output |
| ListText | `Magento\Framework\View\Element\Text\ListText` | Container for child blocks |

---

## 4. Template Block

```php
<?php
declare(strict_types=1);

namespace Vendor\Module\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Vendor\Module\Api\EntityRepositoryInterface;

class EntityList extends Template
{
    public function __construct(
        Context $context,
        private EntityRepositoryInterface $entityRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getEntities(): array
    {
        return $this->entityRepository->getAll();
    }

    public function getEntityUrl($entity): string
    {
        return $this->getUrl('vendor_module/entity/view', ['id' => $entity->getId()]);
    }
}
```

### Template File
```php
<?php
/** @var \Vendor\Module\Block\EntityList $block */
?>
<div class="entity-list">
    <?php foreach ($block->getEntities() as $entity): ?>
        <div class="entity-item">
            <a href="<?= $block->escapeUrl($block->getEntityUrl($entity)) ?>">
                <?= $block->escapeHtml($entity->getName()) ?>
            </a>
        </div>
    <?php endforeach; ?>
</div>
```

---

## 5. Connecting Block to Layout

```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <referenceContainer name="content">
            <block class="Vendor\Module\Block\EntityList"
                   name="entity.list"
                   template="Vendor_Module::entity/list.phtml">
                <arguments>
                    <argument name="view_model" xsi:type="object">
                        Vendor\Module\ViewModel\EntityViewModel
                    </argument>
                </arguments>
            </block>
        </referenceContainer>
    </body>
</page>
```

---

## 6. Block Methods

### Escaping Methods (XSS Protection)
```php
// HTML escape
$block->escapeHtml($string);

// URL escape
$block->escapeUrl($url);

// JavaScript escape
$block->escapeJs($string);

// HTML attribute escape
$block->escapeHtmlAttr($string);

// CSS escape
$block->escapeCss($string);
```

### URL Methods
```php
$block->getUrl('module/controller/action', ['param' => 'value']);
$block->getBaseUrl();
$block->getViewFileUrl('Vendor_Module::js/script.js');
```

### Child Blocks
```php
$block->getChildHtml('child.name');
$block->getChildBlock('child.name');
$block->getChildNames(); // array of child block names
$block->setChild('name', $block); // add child block
$block->unsetChild('name'); // remove child block
```

### Data Methods
```php
$block->getData('key');
$block->setData('key', 'value');
$block->hasData('key');
$block->unsetData('key');
```

---

## 7. Caching

```php
protected function getCacheKeyInfo(): array
{
    return [
        'VENDOR_MODULE_ENTITY_LIST',
        $this->_storeManager->getStore()->getId(),
        $this->_storeManager->getStore()->getCurrentCurrencyCode(),
        $this->getTemplate(),
        $this->getCustomerId() // if customer-specific
    ];
}

protected function getCacheLifetime(): int
{
    return 3600; // 1 hour (null = infinite, false = no cache)
}

protected function getCacheTags(): array
{
    return [
        \Magento\Store\Model\Store::CACHE_TAG,
        'vendor_module_entity'
    ];
}
```

### Disable Cache for Block
```php
protected function _construct()
{
    parent::_construct();
    $this->setData('cache_lifetime', false);
}
```

---

## 8. ViewModels

### ViewModel (Recommended Approach)
```php
<?php
namespace Vendor\Module\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class EntityViewModel implements ArgumentInterface
{
    public function __construct(
        private EntityRepositoryInterface $repository
    ) {}

    public function getEntities(): array
    {
        return $this->repository->getAll();
    }
}
```

### Using in Layout
```xml
<block class="Magento\Framework\View\Element\Template" 
       name="my.block"
       template="Vendor_Module::template.phtml">
    <arguments>
        <argument name="view_model" xsi:type="object">
            Vendor\Module\ViewModel\EntityViewModel
        </argument>
    </arguments>
</block>
```

### Using in Template
```php
<?php
/** @var \Magento\Framework\View\Element\Template $block */
/** @var \Vendor\Module\ViewModel\EntityViewModel $viewModel */
$viewModel = $block->getData('view_model');
$entities = $viewModel->getEntities();
?>
```

---

## 9. Best Practices

### ✅ Use ViewModels
ViewModels are preferred over Blocks for new development - keeps blocks thin.

### ✅ Always Escape Output
```php
<?= $block->escapeHtml($entity->getName()) ?>
```

### ✅ Keep Blocks Thin
Move business logic to Services or ViewModels.

### ✅ Use Type Hints
```php
/** @var \Vendor\Module\Block\EntityList $block */
```

### ✅ Implement Caching
Always define cache keys for better performance.

---

## 10. Core Classes & Paths

### Framework Classes (vendor/magento/framework/)
| Class | Path | Purpose |
|-------|------|---------|
| `AbstractBlock` | `View/Element/AbstractBlock.php` | Base class for all blocks |
| `Template` | `View/Element/Template.php` | Block with template support |
| `Context` | `View/Element/Template/Context.php` | Dependencies container |
| `BlockInterface` | `View/Element/BlockInterface.php` | Block interface |

### What's in Context?
```php
// Template\Context provides these (no need to inject manually):
$this->_storeManager      // Store manager
$this->_eventManager      // Event manager
$this->_urlBuilder        // URL builder
$this->_request           // HTTP request
$this->_layout            // Layout object
$this->_scopeConfig       // Config
$this->_cacheState        // Cache state
$this->_escaper           // Escaper
$this->_filterManager     // Filter manager
$this->_localeDate        // Date/time
$this->_session           // Session
```

---

## 11. Block Lifecycle

### Execution Flow
```
1. Layout XML parsed
       ↓
2. Block object created (DI)
       ↓
3. _construct() called
       ↓
4. setLayout() called
       ↓
5. _prepareLayout() called
       ↓
6. [... other blocks processed ...]
       ↓
7. toHtml() called
       ↓
8. _beforeToHtml() called
       ↓
9. _toHtml() called (renders template)
       ↓
10. _afterToHtml() called
       ↓
11. HTML returned
```

### Key Methods to Override

#### _construct()
```php
// Called after __construct, use for initialization
protected function _construct()
{
    parent::_construct();
    $this->setTemplate('Vendor_Module::my_template.phtml');
}
```

#### _prepareLayout()
```php
// Called when block is added to layout, use for adding child blocks
protected function _prepareLayout()
{
    parent::_prepareLayout();
    
    // Add child blocks, set page title, add breadcrumbs, etc.
    $this->getLayout()->createBlock(SomeBlock::class, 'child.block');
    
    return $this;
}
```

#### _beforeToHtml()
```php
// Called before rendering, last chance to modify data
protected function _beforeToHtml()
{
    $this->setData('items', $this->loadItems());
    return parent::_beforeToHtml();
}
```

#### _toHtml()
```php
// Override to customize rendering
protected function _toHtml()
{
    if (!$this->shouldRender()) {
        return '';
    }
    return parent::_toHtml();
}
```

---

## 12. Data Arguments

### Passing Data via Layout XML
```xml
<block class="Vendor\Module\Block\MyBlock" name="my.block">
    <arguments>
        <argument name="title" xsi:type="string">My Title</argument>
        <argument name="count" xsi:type="number">10</argument>
        <argument name="enabled" xsi:type="boolean">true</argument>
        <argument name="items" xsi:type="array">
            <item name="first" xsi:type="string">Item 1</item>
            <item name="second" xsi:type="string">Item 2</item>
        </argument>
        <argument name="helper" xsi:type="helper">
            Vendor\Module\Helper\Data::getConfig
        </argument>
        <argument name="options" xsi:type="options">
            Vendor\Module\Model\Config\Source\Options
        </argument>
    </arguments>
</block>
```

### Accessing in Block/Template
```php
$block->getData('title');     // "My Title"
$block->getData('count');     // 10
$block->getData('enabled');   // true
$block->getData('items');     // ['first' => 'Item 1', 'second' => 'Item 2']
```

---

## 13. Common Mistakes

### ❌ Business Logic in Block
```php
// BAD
public function getFilteredProducts()
{
    $collection = $this->collectionFactory->create();
    $collection->addFieldToFilter('status', 1);
    $collection->addFieldToFilter('price', ['gt' => 100]);
    // ... complex filtering
    return $collection;
}
```

```php
// GOOD - Use ViewModel or Service
public function getProducts()
{
    return $this->productService->getFilteredProducts();
}
```

### ❌ Not Escaping Output
```php
// BAD - XSS vulnerability
<?= $product->getName() ?>

// GOOD
<?= $block->escapeHtml($product->getName()) ?>
```

### ❌ Injecting Too Many Dependencies
```php
// BAD - Too many dependencies
public function __construct(
    Context $context,
    ProductRepository $productRepo,
    CategoryRepository $categoryRepo,
    CustomerSession $session,
    StoreManager $storeManager,  // Already in Context!
    // ... 10 more
) {}

// GOOD - Use ViewModel or check Context
```

### ❌ Using ObjectManager
```php
// BAD
$product = \Magento\Framework\App\ObjectManager::getInstance()
    ->get(ProductRepository::class);

// GOOD - Use DI
public function __construct(
    Context $context,
    private ProductRepositoryInterface $productRepository
) {}
```

### ❌ Forgetting Parent Constructor
```php
// BAD
public function __construct(
    Context $context,
    private MyService $service
) {
    // Missing parent::__construct()!
}

// GOOD
public function __construct(
    Context $context,
    private MyService $service,
    array $data = []
) {
    parent::__construct($context, $data);
}
```

---

## 14. Debugging Tips

### Check if Block is Rendered
```php
// In template
<?php echo "Block rendered: " . get_class($block); ?>
```

### Enable Template Hints
```bash
bin/magento dev:template-hints:enable
bin/magento cache:flush
```

### Log Block Data
```php
protected function _beforeToHtml()
{
    $this->_logger->debug('Block Data: ' . print_r($this->getData(), true));
    return parent::_beforeToHtml();
}
```

### Check Layout XML
```bash
bin/magento dev:query-log:enable
# Check var/log/ for layout processing
```

### Xdebug Breakpoints
Set breakpoints in:
- `vendor/magento/framework/View/Element/AbstractBlock.php::toHtml()`
- `vendor/magento/framework/View/Element/Template.php::fetchView()`

### Find Which Layout File Adds Block
```bash
grep -r "name=\"block.name\"" app/ vendor/
```

---

## 📌 Summary

| Component | Path |
|-----------|------|
| Block | `Block/MyBlock.php` |
| Template | `view/frontend/templates/my_block.phtml` |
| Layout | `view/frontend/layout/route_controller_action.xml` |
| ViewModel | `ViewModel/MyViewModel.php` |

---

## 🔗 Official Documentation
- [Block Architecture](https://developer.adobe.com/commerce/php/development/components/view-models/)
- [Templates](https://developer.adobe.com/commerce/frontend-core/guide/templates/)
- [Layout Instructions](https://developer.adobe.com/commerce/frontend-core/guide/layouts/)

---

**Last Updated:** 2026
**Author:** Elsherif
