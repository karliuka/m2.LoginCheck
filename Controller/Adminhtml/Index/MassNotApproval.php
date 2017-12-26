<?php
/**
 * Copyright © 2011-2017 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\LoginCheck\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassNotApproval
 */
class MassNotApproval 
	extends \Magento\Customer\Controller\Adminhtml\Index\AbstractMassAction
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
	
    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $customerMapper;
	
    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerDataFactory;
	
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;	
	
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
		CustomerRepositoryInterface $customerRepository,
		\Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
		\Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
		\Magento\Customer\Model\Customer\Mapper $customerMapper
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        
		$this->customerRepository = $customerRepository;
		$this->customerDataFactory = $customerDataFactory;
		$this->dataObjectHelper = $dataObjectHelper;
		$this->customerMapper = $customerMapper;
    }

    /**
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
            $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $customersUpdated));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
