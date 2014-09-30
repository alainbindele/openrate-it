<?php

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;


/**
 *
 * Class UserMenuWidget
 * @package Application\View\Helper
 *
 */
class UserMenuWidget extends AbstractHelper
{
    
    /**
     * $var string template used for view
     */
    protected $viewTemplate;
    /**
     * __invoke
     *
     * @access public
     * @param array $options array of options
     * @return string
     */
    public function __invoke($options = array())
    {
    	$vm = new ViewModel(array());
    	$vm->setTemplate($this->viewTemplate);
   		return $this->getView()->render($vm);
    	
    }


    /**
     * @param string $viewTemplate
     * @return UserMenuWidget
     */
    public function setViewTemplate($viewTemplate)
    {
        $this->viewTemplate = $viewTemplate;
        return $this;
    }

}
