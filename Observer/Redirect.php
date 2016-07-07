<?php
/**
 * Faonni
 *  
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade module to newer
 * versions in the future.
 * 
 * @package     Faonni_LoginCheck
 * @copyright   Copyright (c) 2016 Karliuka Vitalii(karliuka.vitalii@gmail.com) 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Faonni\LoginCheck\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;

class Redirect implements ObserverInterface
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