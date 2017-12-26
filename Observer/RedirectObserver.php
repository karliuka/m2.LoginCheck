<?php
/**
 * Copyright © 2011-2017 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\LoginCheck\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;

class RedirectObserver implements ObserverInterface
{
    /** 
	 * @var Registration 
	 */
    protected $registration;

    /**
     * @var Session
     */
    protected $session;
	
    /**
     * @var Url
     */	
    protected $url;
	
    /**
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
     * @var array
     */
    protected $registrationAction = array( 
		'create', 
		'createpost'
	);
	
    /**
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
     * Customer redirect event handler
     *
     * @return void
     */
	public function execute(\Magento\Framework\Event\Observer $observer) 
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
     * Throw control to different action (control and module if was specified).
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