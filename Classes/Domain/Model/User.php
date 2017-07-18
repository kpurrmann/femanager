<?php
namespace In2code\Femanager\Domain\Model;

use In2code\Femanager\Utility\UserUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Alex Kellner <alexander.kellner@in2code.de>, in2code
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * User Model
 *
 * @package femanager
 * @license http://www.gnu.org/licenses/gpl.html
 *          GNU General Public License, version 3 or later
 */
class User extends FrontendUser
{

    /**
     * txFemanagerChangerequest
     *
     * @var string
     */
    protected $txFemanagerChangerequest;

    /**
     * crdate
     *
     * @var \DateTime
     */
    protected $crdate;

    /**
     * tstamp
     *
     * @var \DateTime
     */
    protected $tstamp;

    /**
     * disable
     *
     * @var bool
     */
    protected $disable;

    /**
     * txFemanagerConfirmedbyuser
     *
     * @var bool
     */
    protected $txFemanagerConfirmedbyuser;

    /**
     * txFemanagerConfirmedbyadmin
     *
     * @var bool
     */
    protected $txFemanagerConfirmedbyadmin;

    /**
     * Online Status
     *
     * @var bool
     */
    protected $isOnline = false;

    /**
     * ignoreDirty (TRUE disables update)
     *
     * @var bool
     */
    protected $ignoreDirty = false;

    /**
     * gender
     *
     * @var integer
     */
    protected $gender;

    /**
     * dateOfBirth
     *
     * @var \DateTime
     */
    protected $dateOfBirth;

    /**
     * termsAndConditions
     *
     * @var bool
     */
    protected $terms;

    /**
     * txExtbaseType
     *
     * @var string
     */
    protected $txExtbaseType;

    /**
     * Created Password in Cleartext (if generated Password)
     * will of course not be persistent and lives until runtime end
     *
     * @var string
     */
    protected $passwordAutoGenerated = '';

    /**
     * Remove all usergroups
     * @return void
     */
    public function removeAllUsergroups()
    {
        $this->usergroup = new ObjectStorage();
    }

    /**
     * @param string $txFemanagerChangerequest
     * @return User
     */
    public function setTxFemanagerChangerequest($txFemanagerChangerequest)
    {
        $this->txFemanagerChangerequest = $txFemanagerChangerequest;
        return $this;
    }

    /**
     * @return string
     */
    public function getTxFemanagerChangerequest()
    {
        return $this->txFemanagerChangerequest;
    }

    /**
     * @param \DateTime $crdate
     * @return User
     */
    public function setCrdate($crdate)
    {
        $this->crdate = $crdate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * @param \DateTime $tstamp
     * @return User
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * @param boolean $disable
     * @return User
     */
    public function setDisable($disable)
    {
        $this->disable = $disable;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getDisable()
    {
        return $this->disable;
    }

    /**
     * @param \bool $txFemanagerConfirmedbyadmin
     * @return User
     */
    public function setTxFemanagerConfirmedbyadmin($txFemanagerConfirmedbyadmin)
    {
        $this->txFemanagerConfirmedbyadmin = $txFemanagerConfirmedbyadmin;
        return $this;
    }

    /**
     * @return \bool
     */
    public function getTxFemanagerConfirmedbyadmin()
    {
        return $this->txFemanagerConfirmedbyadmin;
    }

    /**
     * @param \bool $txFemanagerConfirmedbyuser
     * @return User
     */
    public function setTxFemanagerConfirmedbyuser($txFemanagerConfirmedbyuser)
    {
        $this->txFemanagerConfirmedbyuser = $txFemanagerConfirmedbyuser;
        return $this;
    }

    /**
     * @return \bool
     */
    public function getTxFemanagerConfirmedbyuser()
    {
        return $this->txFemanagerConfirmedbyuser;
    }

    /**
     * @param boolean $ignoreDirty
     * @return User
     */
    public function setIgnoreDirty($ignoreDirty)
    {
        $this->ignoreDirty = $ignoreDirty;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIgnoreDirty()
    {
        return $this->ignoreDirty;
    }

    /**
     * Returns the gender
     *
     * @return integer $gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Sets the gender
     *
     * @param integer $gender
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * Returns the dateOfBirth
     *
     * @return \DateTime $dateOfBirth
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * Sets the dateOfBirth
     *
     * @param \DateTime $dateOfBirth
     * @return User
     */
    public function setDateOfBirth($dateOfBirth)
    {
        if ($dateOfBirth instanceof \DateTime) {
            $dateOfBirth->setTime(0, 0, 0);
        }
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    /**
     *  Returns, whether the user has accepted terms and conditions
     *
     * @return boolean
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * Set whether the user has accepted terms and conditions
     *
     * @param $terms
     * @return User
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;
        return $this;
    }

    /**
     * Check if last FE login was within the last 2h
     *
     * @return boolean
     */
    public function isOnline()
    {
        if (
            method_exists($this->getLastlogin(), 'getTimestamp')
            && $this->getLastlogin()->getTimestamp() > (time() - 2 * 60 * 60)
            && UserUtility::checkFrontendSessionToUser($this)
        ) {
            return true;
        }
        return $this->isOnline;
    }

    /**
     * @param string $txExtbaseType
     * @return User
     */
    public function setTxExtbaseType($txExtbaseType)
    {
        $this->txExtbaseType = $txExtbaseType;
        return $this;
    }

    /**
     * @return string
     */
    public function getTxExtbaseType()
    {
        return $this->txExtbaseType;
    }

    /**
     * @param string $passwordAutoGenerated
     * @return User
     */
    public function setPasswordAutoGenerated($passwordAutoGenerated)
    {
        $this->passwordAutoGenerated = $passwordAutoGenerated;
        return $this;
    }

    /**
     * @return string
     */
    public function getPasswordAutoGenerated()
    {
        return $this->passwordAutoGenerated;
    }

    /**
     * Workaround to disable persistence in updateAction
     *
     * @param null $propertyName
     * @return bool
     */
    public function _isDirty($propertyName = null)
    {
        return $this->getIgnoreDirty() ? false : parent::_isDirty($propertyName);
    }

    /**
     * @return string of the email domain
     */
    public function getEmailDomain() {
        $emailDomain = '';
        if ($this->getEmail() && strrchr($this->getEmail(), "@")) {
            $emailDomain = substr(strrchr($this->getEmail(), "@"), 1);
        }
        return $emailDomain;
    }
}
