<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Add loyalty_points attribute to products
 */
class AddLoyaltyPointsAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // Check if attribute already exists
        if (!$eavSetup->getAttributeId(Product::ENTITY, 'loyalty_points')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'loyalty_points',
                [
                    'type' => 'int',
                    'label' => 'Loyalty Points',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 100,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Loyalty Program',
                    'visible' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'default' => 0,
                    'frontend_class' => 'validate-digits',
                    'note' => 'Points customer will earn when purchasing this product. Leave 0 to use default calculation.'
                ]
            );

            // Add to all attribute sets
            $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
            $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);

            foreach ($attributeSetIds as $attributeSetId) {
                $groupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Loyalty Program');
                
                if (!$groupId) {
                    $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, 'Loyalty Program', 100);
                    $groupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Loyalty Program');
                }

                $eavSetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $groupId,
                    'loyalty_points',
                    100
                );
            }
        }

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
