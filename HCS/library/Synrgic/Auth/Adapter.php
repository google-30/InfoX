<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * category   ZendX
 * package    ZendX_Doctrine
 * subpackage ZendX_Doctrine_Auth_Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */


/**
 * @see Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';

/**
 * @see Zend_Auth_Result
 */
require_once 'Zend/Auth/Result.php';


/**
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Synrgic_Auth_Adapter implements Zend_Auth_Adapter_Interface
{
    /**
     * Database Entity Manager
     *
     * @var Doctrine\ORM\EntityManager
     */
    protected $_em = null;

    /**
     * $_entityName - the Etntity Name
     *
     * @var string
     */
    protected $_entityName = null;

    /**
     * $_identityColumn - the column to use as the identity
     *
     * @var string
     */
    protected $_identityColumn = null;

    /**
     * $_credentialColumns - columns to be used as the credentials
     *
     * @var string
     */
    protected $_credentialColumn = null;

    /**
     * $_identity - Identity value
     *
     * @var string
     */
    protected $_identity = null;

    /**
     * $_credential - Credential values
     *
     * @var string
     */
    protected $_credential = null;

    /**
     * $_authenticateResultInfo
     *
     * @var array
     */
    protected $_authenticateResultInfo = null;
    
    /**
     * $_resultObject - Results of database authentication query
     *
     * @var array
     */
    protected $_resultObject = false;

    /**
     * __construct() - Sets configuration options
     *
     * @param  Doctrine\ORM\EntityManager   $em
     * @param  string                   $entityName
     * @param  string                   $identityColumn
     * @param  string                   $credentialColumn
     * @return void
     */
    public function __construct(Doctrine\ORM\EntityManager $em, $entityName = null, $identityColumn = null,
                                $credentialColumn = null )
    {
	assert( $em != null );
	$this->_em = $em;

        if (null !== $entityName) {
            $this->setEntityName($entityName);
        }

        if (null !== $identityColumn) {
            $this->setIdentityColumn($identityColumn);
        }

        if (null !== $credentialColumn) {
            $this->setCredentialColumn($credentialColumn);
        }

    }
    
    /**
     * setEntityName() - set the table name to be used in the select query
     *
     * @param  string $entityName
     * @return Synrgic_Auth_Adapter Provides a fluent interface
     */
    public function setEntityName($entityName)
    {
        $this->_entityName = $entityName;
        return $this;
    }

    /**
     * setIdentityColumn() - set the column name to be used as the identity column
     *
     * @param  string $identityColumn
     * @return Synrgic_Auth_Adapter Provides a fluent interface
     */
    public function setIdentityColumn($identityColumn)
    {
        $this->_identityColumn = $identityColumn;
        return $this;
    }

    /**
     * setCredentialColumn() - set the column name to be used as the credential column
     *
     * @param  string $credentialColumn
     * @return Synrgic_Auth_Adapter Provides a fluent interface
     */
    public function setCredentialColumn($credentialColumn)
    {
        $this->_credentialColumn = $credentialColumn;
        return $this;
    }

    /**
     * setIdentity() - set the value to be used as the identity
     *
     * @param  string $value
     * @return Synrgic_Auth_Adapter Provides a fluent interface
     */
    public function setIdentity($value)
    {
        $this->_identity = $value;
        return $this;
    }

    /**
     * setCredential() - set the credential value to be used, optionally can specify a treatment
     * to be used, should be supplied in parameterized form, such as 'MD5(?)' or 'PASSWORD(?)'
     *
     * @param  string $credential
     * @return Synrgic_Auth_Adapter Provides a fluent interface
     */
    public function setCredential($credential)
    {
        $this->_credential = $credential;
        return $this;
    }

    /**
     * getResultUser() - Returns the result Object
     *
     * @return false|ValidObject of entityName
     */
    public function getResultObject()
    {
	return $this->_resultObject;
    }

    /**
     * authenticate() - defined by Zend_Auth_Adapter_Interface.  This method is called to 
     * attempt an authentication.  Previous to this call, this adapter would have already
     * been configured with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @throws Zend_Auth_Adapter_Exception if answering the authentication query is impossible
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $this->_authenticateSetup();
        $query = $this->_getQuery();
        $resultIdentities = $this->_performQuery($query);
        $authResult = $this->_validateResults($resultIdentities);
        return $authResult;
    }

    /**
     * _authenticateSetup() - This method abstracts the steps involved with making sure
     * that this adapter was indeed setup properly with all required peices of information.
     *
     * @throws Zend_Auth_Adapter_Exception - in the event that setup was not done properly
     * @return true
     */
    protected function _authenticateSetup()
    {
        $exception = null;
        
        if ($this->_entityName == '') {
            $exception = 'A table must be supplied for the Synrgic_Auth_Adapter authentication adapter.';
        } elseif ($this->_identityColumn == '') {
            $exception = 'An identity column must be supplied for the Synrgic_Auth_Adapter authentication adapter.';
        } elseif ($this->_credentialColumn == '') {
            $exception = 'A credential column must be supplied for the Synrgic_Auth_Adapter authentication adapter.';
        } elseif ($this->_identity == '') {
            $exception = 'A value for the identity was not provided prior to authentication with Synrgic_Auth_Adapter.';
        } elseif ($this->_credential === null) {
            $exception = 'A credential value was not provided prior to authentication with Synrgic_Auth_Adapter.';
        }

        if (null !== $exception) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception($exception);
        }
        
        $this->_authenticateResultInfo = array(
            'code'     => Zend_Auth_Result::FAILURE,
            'identity' => $this->_identity,
            'messages' => array()
            );
            
        return true;
    }

    /**
     * _getQuery() - This method creates a Zend_Db_Select object that
     * is completely configured to be queried against the database.
     *
     * @return Doctrine\ORM\Query
     */
    protected function _getQuery()
    {

	$qb = $this->_em->createQueryBuilder();
	$qb ->select('u')
 	   ->from($this->_entityName,'u')
	   ->where('u.' . $qb->expr()->eq($this->_identityColumn, ':identity')); 
	$qb->setParameter('identity', $this->_identity);

        return $qb->getQuery();
    }

    /**
     * _performQuery() - This method accepts a Doctrine\ORM\Query object and
     * performs a query against the database with that object.
     *
     * @param Doctrine\ORM\Query $dbSelect
     * @throws Zend_Auth_Adapter_Exception - when a invalid select object is encoutered
     * @return array
     */
    protected function _performQuery(Doctrine\ORM\Query $dbSelect)
    {
        try {
	    $resultIdentities = $dbSelect->getResult();
        } catch (Exception $e) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception('Synrgic_Auth_Adapter Query Error: '.  $e->getMessage());
        }
        return $resultIdentities;
    }

    /**
     * _validateResults() - This method attempts to make certian that only one
     * record was returned in the result set as well as performing the actual
     * authentication if required
     *
     * @param array $resultIdentities
     * @return true|Zend_Auth_Result
     */
    protected function _validateResults(array $resultIdentities)
    {
        if (count($resultIdentities) < 1) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $this->_authenticateResultInfo['messages'][] = 'A record with the supplied identity could not be found.';
            return $this->_authenticateCreateAuthResult();
        } elseif (count($resultIdentities) > 1) {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS;
            $this->_authenticateResultInfo['messages'][] = 'More than one record matches the supplied identity.';
            return $this->_authenticateCreateAuthResult();
        }

	// Only one entry at this point
	$resultIdentity = $resultIdentities[0];

	$helper = new Synrgic_Models_PasswordHelper();
	if ($helper->checkPassword($resultIdentity->{$this->_credentialColumn}, $this->_credential) == false){
	    $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
	    $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
	    return $this->_authenticateCreateAuthResult();
	}  else {
	    $this->_resultObject = $resultIdentity;
	    $this->_authenticateResultInfo['code'] = Zend_Auth_Result::SUCCESS;
	    $this->_authenticateResultInfo['messages'][] = 'Authentication successful.';
	    return $this->_authenticateCreateAuthResult();
	}
    }

    /**
     * _authenticateCreateAuthResult() - This method creates a Zend_Auth_Result object
     * from the information that has been collected during the authenticate() attempt.
     *
     * @return Zend_Auth_Result
     */
    protected function _authenticateCreateAuthResult()
    {
        return new Zend_Auth_Result(
            $this->_authenticateResultInfo['code'],
            $this->_authenticateResultInfo['identity'],
            $this->_authenticateResultInfo['messages']
            );
    }

}

?>
