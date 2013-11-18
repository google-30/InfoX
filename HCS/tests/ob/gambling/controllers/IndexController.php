<?php

class Gambling_IndexController extends Zend_Controller_Action
{
	private $session;

    public function init()
    {
        /* Initialize action controller here */
		$this->session = Zend_Registry::get(SYNRGIC_SESSION);
    }

    public function indexAction()
    {
        // action body

		$this->forward('login');
		//$this->forward('blackJack');
    }

	public function loginAction()
    {
		if($this->session->is_guest_with_pin){
			$this->_redirect('gambling/index/blackjack');	
		}
		else {
		    // action body
			$guest = Zend_Auth::getInstance()->getIdentity();
			$this->view->pin=$guest->pin;
			$this->view->form=$this->getForm();

			$request = $this->getRequest();
			$form = $this->getForm();

			if($request->isPost() && $form->isValid($request->getPost())){
				$pin = $form->getValue('pin');
				$this->view->pin=$pin;

				if('a'.$pin == 'a'.$guest->pin){
					$this->session->is_guest_with_pin=true;
					$this->forward('blackJack');
					$this->_redirect('gambling/index/blackjack');			
				}else{
					$this->view->errorMessage = 'Pin error';
				}
			}
		}
    }

	public function logoutAction()
    {
		if($this->session->is_guest_with_pin){
			$this->session->is_guest_with_pin=false;
		}
		$this->_redirect('gambling/index/login');	
	}

	public function blackjackAction()
    {
        if($this->session->is_guest_with_pin){
			//$this->_redirect('gambling/index/blackjack');	
		}else{
			$this->_redirect('gambling/index/login');	
		}
		
    }

	private function getForm()
    {
        $form = new Synrgic_Models_Form(); 

		$pin = new Zend_Form_Element_Password('pin');  
		$pin->setLabel('Pin:')  
			->setRequired(true);  

		$last = new Zend_Form_Element_Hidden('return');
		$last->setValue($this->_helper->URIHolder->getURI());

		$form->setAction('/gambling/index/login')  
			->setMethod('post')  
			->addElement($pin)  
			->addElement($last)
			->addSubmitButton('Login','Login');

        return $form;
    }



}

