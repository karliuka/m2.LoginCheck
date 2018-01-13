<?php
/**
 * Copyright © 2011-2018 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\LoginCheck\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Backend\App\Action\Context;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\Customer\Mapper as CustomerMapper;
use Magento\Customer\Controller\Adminhtml\Index\AbstractMassAction;

/**
 * MassNotApproval Controller
 */
class MassNotApproval extends AbstractMassAction
{
    /**
     * Customer Repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
	
    /**
     * Customer Mapper
     *
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $customerMapper;
	
    /**
     * Customer Interface Factory
     *
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerDataFactory;
	
    /**
     * Data Object Helper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;	
	
    /**
     * Initialize Controller
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerMapper $customerMapper
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
		CustomerRepositoryInterface $customerRepository,
		CustomerInterfaceFactory $customerDataFactory,
		DataObjectHelper $dataObjectHelper,
		CustomerMapper $customerMapper
    ) {
		$this->customerRepository = $customerRepository;
		$this->customerDataFactory = $customerDataFactory;
		$this->dataObjectHelper = $dataObjectHelper;
		$this->customerMapper = $customerMapper;
		
		parent::__construct(
			$context, 
			$filter, 
			$collectionFactory
		);
    }

    /**
     * Mass Action
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $customersUpdated = 0;
        foreach ($collection->getAllIds() as $customerId) {
            // Verify customer exists
            $savedCustomerData = $this->customerRepository->getById($customerId);
			$customerData = $this->customerMapper->toFlatArray($savedCustomerData);
			
            $customerData['id'] = $customerId;			
			$customerData['is_approval'] = false;
			
			$customer = $this->customerDataFactory->create();
			$this->dataObjectHelper->populateWithArray(
				$customer,
				$customerData,
				'\Magento\Customer\Api\Data\CustomerInterface'
			);			

            $this->customerRepository->save($customer);
            $customersUpdated++;
        }

        if ($customersUpdated) {
            $this->messageManager->addSuccess(
				__('A total of %1 record(s) were updated.', $customersUpdated)
			);
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
