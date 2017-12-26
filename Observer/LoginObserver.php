<?php
/**
 * Copyright © 2011-2017 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\LoginCheck\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;

/**
 * Customer log observer.
 */
class LoginObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $session;
	
    /**
     * @param Session $customerSession 
     */
    public function __construct(
        Session $customerSession
    ) {
        $this->session = $customerSession;
    }
	
    /**
     * Handler for 'customer_login' event.
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
		throw new \Exception(__('This account is not confirmed.'));
    }
}
