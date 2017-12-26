<?php
/**
 * Copyright © 2011-2017 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\LoginCheck\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;

/**
 * Redirect Observer
 */
class RedirectObserver implements ObserverInterface
{
    /**
     * Customer Registration
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $registration;

    /**
     * Customer Session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $session;
	
    /**
     * Url Interface
     *
     * @var \Magento\Framework\UrlInterface
     */	
    protected $url;
	
    /**
     * Allowed Action
     *
     * @var array
     */
    protected $allowedAction = array(
		'login', 
		'loginpost', 
		'forgotpassword', 
		'forgotpasswordpost', 
		'confirmation', 
		'confirm', 
		'logout', 
		'logoutsuccess'
	);
	
    /**
     * Registration Action
     *
     * @var array
     */
    protected $registrationAction = array( 
		'create', 
		'createpost'
	);
	
    /**
     * Initialize Observer
     *
     * @param Session $customerSession
     * @param Registration $registration	 
     * @param UrlInterface $url	 
     */
    public function __construct(
        Session $customerSession,
        Registration $registration,
		UrlInterface $url
    ) {
        $this->session = $customerSession;
        $this->registration = $registration;
		$this->url = $url;
    }
	
    /**
     * Redirect Event
     *
     * @return void
     */
	public function execute(Observer $observer) 
	{
        if ($this->session->isLoggedIn()) {
			return;
        }
        
		$request = $observer->getEvent()->getRequest();
		if ('customer' == $request->getModuleName() && 'account' == $request->getControllerName()) {
			$actionName = strtolower($request->getActionName());
			
			if ($this->registration->isAllowed()) {
				$this->allowedAction = array_merge($this->allowedAction, $this->registrationAction);
			}
			
			if (in_array($actionName, $this->allowedAction)) {
				return;
			}
		}
		$this->_forward($request, 'login', 'account', 'customer');
	}
	
    /**
     * Throw Control To Different Action (Control And Module If Was Specified)
     *
     * @param RequestInterface $request	 
     * @param string $action
     * @param string|null $controller
     * @param string|null $module
     * @return void
     */
    protected function _forward($request, $action, $controller=null, $module=null)
    {
        $request->initForward();
        if (isset($controller)) {
            $request->setControllerName($controller);

            // Module should only be reset if controller has been specified
            if (isset($module)) {
                $request->setModuleName($module);
            }
        }
        $request->setActionName($action);
        $request->setDispatched(false);
    }	
}