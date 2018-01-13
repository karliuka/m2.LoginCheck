<?php
/**
 * Copyright © 2011-2018 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\LoginCheck\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;

/**
 * Login Observer
 */
class LoginObserver implements ObserverInterface
{
    /**
     * Customer Session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $session;
	
    /**
     * Initialize Observer
     *
     * @param Session $customerSession 
     */
    public function __construct(
        Session $customerSession
    ) {
        $this->session = $customerSession;
    }
	
    /**
     * Customer Login Event
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
		if ($customer->getIsApproval()) {
			return;
		}
		
		$this->session->logout();
		throw new \Exception(
			__('This account is not confirmed.')
		);
    }
}
