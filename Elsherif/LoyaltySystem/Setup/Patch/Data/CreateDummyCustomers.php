<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\State;

/**
 * Create dummy customers for testing
 */
class CreateDummyCustomers implements DataPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;
    private CustomerInterfaceFactory $customerFactory;
    private CustomerRepositoryInterface $customerRepository;
    private AddressInterfaceFactory $addressFactory;
    private AddressRepositoryInterface $addressRepository;
    private StoreManagerInterface $storeManager;
    private EncryptorInterface $encryptor;
    private State $state;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        AddressInterfaceFactory $addressFactory,
        AddressRepositoryInterface $addressRepository,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        State $state
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->addressFactory = $addressFactory;
        $this->addressRepository = $addressRepository;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->state = $state;
    }

    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
            // Area code already set
        }

        $customers = [
            [
                'email' => 'john.doe@example.com',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'password' => 'Test@123',
                'address' => [
                    'street' => '123 Main Street',
                    'city' => 'New York',
                    'region' => 'NY',
                    'postcode' => '10001',
                    'country' => 'US',
                    'telephone' => '555-123-4567'
                ]
            ],
            [
                'email' => 'jane.smith@example.com',
                'firstname' => 'Jane',
                'lastname' => 'Smith',
                'password' => 'Test@123',
                'address' => [
                    'street' => '456 Oak Avenue',
                    'city' => 'Los Angeles',
                    'region' => 'CA',
                    'postcode' => '90001',
                    'country' => 'US',
                    'telephone' => '555-234-5678'
                ]
            ],
            [
                'email' => 'ahmed.mohamed@example.com',
                'firstname' => 'Ahmed',
                'lastname' => 'Mohamed',
                'password' => 'Test@123',
                'address' => [
                    'street' => '789 Nile Street',
                    'city' => 'Cairo',
                    'region' => 'Cairo',
                    'postcode' => '11511',
                    'country' => 'EG',
                    'telephone' => '20-123-456789'
                ]
            ],
            [
                'email' => 'sara.ali@example.com',
                'firstname' => 'Sara',
                'lastname' => 'Ali',
                'password' => 'Test@123',
                'address' => [
                    'street' => '321 Palm Road',
                    'city' => 'Dubai',
                    'region' => 'Dubai',
                    'postcode' => '00000',
                    'country' => 'AE',
                    'telephone' => '971-50-1234567'
                ]
            ],
            [
                'email' => 'test.user@example.com',
                'firstname' => 'Test',
                'lastname' => 'User',
                'password' => 'Test@123',
                'address' => [
                    'street' => '999 Test Lane',
                    'city' => 'London',
                    'region' => 'London',
                    'postcode' => 'SW1A 1AA',
                    'country' => 'GB',
                    'telephone' => '44-20-12345678'
                ]
            ]
        ];

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $storeId = $this->storeManager->getStore()->getId();

        foreach ($customers as $customerData) {
            try {
                // Check if customer exists
                try {
                    $existingCustomer = $this->customerRepository->get($customerData['email']);
                    continue; // Skip if exists
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    // Customer doesn't exist, create it
                }

                // Create customer
                $customer = $this->customerFactory->create();
                $customer->setEmail($customerData['email'])
                    ->setFirstname($customerData['firstname'])
                    ->setLastname($customerData['lastname'])
                    ->setWebsiteId($websiteId)
                    ->setStoreId($storeId);

                $hashedPassword = $this->encryptor->getHash($customerData['password'], true);
                $savedCustomer = $this->customerRepository->save($customer, $hashedPassword);

                // Create address
                $addressData = $customerData['address'];
                $address = $this->addressFactory->create();
                $address->setCustomerId($savedCustomer->getId())
                    ->setFirstname($customerData['firstname'])
                    ->setLastname($customerData['lastname'])
                    ->setStreet([$addressData['street']])
                    ->setCity($addressData['city'])
                    ->setRegion($this->getRegion($addressData['region']))
                    ->setPostcode($addressData['postcode'])
                    ->setCountryId($addressData['country'])
                    ->setTelephone($addressData['telephone'])
                    ->setIsDefaultBilling(true)
                    ->setIsDefaultShipping(true);

                $this->addressRepository->save($address);

            } catch (\Exception $e) {
                // Skip failed customers
                continue;
            }
        }

        $this->moduleDataSetup->endSetup();
        return $this;
    }

    private function getRegion(string $regionName): \Magento\Customer\Api\Data\RegionInterface
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $regionFactory = $objectManager->get(\Magento\Customer\Api\Data\RegionInterfaceFactory::class);
        
        $region = $regionFactory->create();
        $region->setRegion($regionName);
        
        return $region;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
