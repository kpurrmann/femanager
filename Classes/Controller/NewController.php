<?php
declare(strict_types=1);
namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\HashUtility;
use In2code\Femanager\Utility\LocalizationUtility;
use In2code\Femanager\Utility\LogUtility;
use In2code\Femanager\Utility\StringUtility;
use In2code\Femanager\Utility\UserUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * Class NewController
 */
class NewController extends AbstractController
{

    /**
     * Render registration form
     *
     * @param User $user
     * @return void
     */
    public function newAction(User $user = null)
    {
        $this->view->assignMultiple(
            [
                'user' => $user,
                'allUserGroups' => $this->allUserGroups
            ]
        );
        $this->assignForAll();
    }

    /**
     * action create
     *
     * @param User $user
     * @validate $user In2code\Femanager\Domain\Validator\ServersideValidator
     * @validate $user In2code\Femanager\Domain\Validator\PasswordValidator
     * @validate $user In2code\Femanager\Domain\Validator\CaptchaValidator
     * @return void
     */
    public function createAction(User $user)
    {
        $user = UserUtility::overrideUserGroup($user, $this->settings);
        $user = FrontendUtility::forceValues($user, $this->config['new.']['forceValues.']['beforeAnyConfirmation.']);
        $user = UserUtility::fallbackUsernameAndPassword($user);
        $user = UserUtility::takeEmailAsUsername($user, $this->settings);
        UserUtility::hashPassword($user, $this->settings['new']['misc']['passwordSave']);
        $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'BeforePersist', [$user, $this]);

        if ($this->isAllConfirmed()) {
            $this->createAllConfirmed($user);
        } else {
            $this->createRequest($user);
        }
    }

    /**
     * Dispatcher action for every confirmation request
     *
     * @param int $user User UID (user could be hidden)
     * @param string $hash Given hash
     * @param string $status
     *            "userConfirmation", "userConfirmationRefused", "adminConfirmation",
     *            "adminConfirmationRefused", "adminConfirmationRefusedSilent"
     * @return void
     */
    public function confirmCreateRequestAction($user, $hash, $status = 'adminConfirmation')
    {
        $user = $this->userRepository->findByUid($user);
        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__ . 'BeforePersist',
            [$user, $hash, $status, $this]
        );
        if ($user === null) {
            $this->addFlashMessage(LocalizationUtility::translate('missingUserInDatabase'), '', FlashMessage::ERROR);
            $this->redirect('new');
        }

        switch ($status) {
            case 'userConfirmation':
                $furtherFunctions = $this->statusUserConfirmation($user, $hash, $status);
                break;

            case 'userConfirmationRefused':
                $furtherFunctions = $this->statusUserConfirmationRefused($user, $hash);
                break;

            case 'adminConfirmation':
                $furtherFunctions = $this->statusAdminConfirmation($user, $hash, $status);
                break;

            case 'adminConfirmationRefused':
                // Admin refuses profile
            case 'adminConfirmationRefusedSilent':
                $furtherFunctions = $this->statusAdminConfirmationRefused($user, $hash, $status);
                break;

            default:
                $furtherFunctions = false;

        }

        if ($furtherFunctions) {
            $this->redirectByAction('new', $status . 'Redirect');
        }
        $this->redirect('new');
    }

    /**
     * Status action: User confirmation
     *
     * @param User $user
     * @param string $hash
     * @param string $status
     * @return bool allow further functions
     * @throws UnsupportedRequestTypeException
     * @throws IllegalObjectTypeException
     */
    protected function statusUserConfirmation(User $user, $hash, $status)
    {
        if (HashUtility::validHash($hash, $user)) {
            if ($user->getTxFemanagerConfirmedbyuser()) {
                $this->addFlashMessage(LocalizationUtility::translate('userAlreadyConfirmed'), '', FlashMessage::ERROR);
                $this->redirect('new');
            }

            $user = FrontendUtility::forceValues($user, $this->config['new.']['forceValues.']['onUserConfirmation.']);
            $user->setTxFemanagerConfirmedbyuser(true);
            $this->userRepository->update($user);
            $this->persistenceManager->persistAll();
            LogUtility::log(Log::STATUS_REGISTRATIONCONFIRMEDUSER, $user);

            if ($this->isAdminConfirmationMissing($user)) {

                $this->sendMailService->send(
                    'createAdminConfirmation',
                    StringUtility::makeEmailArray(
                        $this->settings['new']['confirmByAdmin'],
                        $this->settings['new']['email']['createAdminConfirmation']['receiver']['name']['value']
                    ),
                    StringUtility::makeEmailArray($user->getEmail(), $user->getUsername()),
                    'New Registration request',
                    [
                        'user' => $user,
                        'hash' => HashUtility::createHashForUser($user)
                    ],
                    $this->config['new.']['email.']['createAdminConfirmation.']
                );
                $this->addFlashMessage(LocalizationUtility::translate('createRequestWaitingForAdminConfirm'));

            } else {
                $user->setDisable(false);
                LogUtility::log(Log::STATUS_NEWREGISTRATION, $user);
                $this->finalCreate($user, 'new', 'createStatus', true, $status);
            }

        } else {
            $this->addFlashMessage(LocalizationUtility::translate('createFailedProfile'), '', FlashMessage::ERROR);

            return false;
        }

        return true;
    }

    /**
     * Status action: User confirmation refused
     *
     * @param User $user
     * @param string $hash
     * @return bool allow further functions
     * @throws IllegalObjectTypeException
     */
    protected function statusUserConfirmationRefused(User $user, $hash)
    {
        if (HashUtility::validHash($hash, $user)) {
            LogUtility::log(Log::STATUS_REGISTRATIONREFUSEDUSER, $user);
            $this->addFlashMessage(LocalizationUtility::translate('createProfileDeleted'));
            $this->userRepository->remove($user);
        } else {
            $this->addFlashMessage(LocalizationUtility::translate('createFailedProfile'), '', FlashMessage::ERROR);

            return false;
        }

        return true;
    }

    /**
     * Status action: Admin confirmation
     *
     * @param User $user
     * @param string $hash
     * @param string $status
     * @return bool allow further functions
     */
    protected function statusAdminConfirmation(User $user, $hash, $status)
    {
        if (HashUtility::validHash($hash, $user)) {
            $this->doApprovement($user, $status);
        } else {
            $this->addFlashMessage(LocalizationUtility::translate('createFailedProfile'), '', FlashMessage::ERROR);

            return false;
        }

        return true;
    }

    // @todo add action ConfirmUser


    // @todo add action RefuseUser

    /**
     * Status action: Admin refused profile creation (normal or silent)
     *
     * @param User $user
     * @param $hash
     * @param $status
     * @return bool allow further functions
     * @throws IllegalObjectTypeException
     */
    protected function statusAdminConfirmationRefused(User $user, $hash, $status)
    {
        if (HashUtility::validHash($hash, $user)) {
            LogUtility::log(Log::STATUS_REGISTRATIONREFUSEDADMIN, $user);
            $this->addFlashMessage(LocalizationUtility::translate('createProfileDeleted'));
            if ($status !== 'adminConfirmationRefusedSilent') {
                $this->sendMailService->send(
                    'CreateUserNotifyRefused',
                    StringUtility::makeEmailArray(
                        $user->getEmail(),
                        $user->getFirstName() . ' ' . $user->getLastName()
                    ),
                    ['sender@femanager.org' => 'Sender Name'],
                    'Your profile was refused',
                    ['user' => $user],
                    $this->config['new.']['email.']['createUserNotifyRefused.']
                );
            }
            $this->userRepository->remove($user);
        } else {
            $this->addFlashMessage(LocalizationUtility::translate('createFailedProfile'), '', FlashMessage::ERROR);

            return false;
        }

        return true;
    }

    /**
     * Just for showing informations after user creation
     *
     * @return void
     */
    public function createStatusAction()
    {
    }

    /**
     * @return bool
     */
    protected function isAllConfirmed()
    {
        return empty($this->settings['new']['confirmByUser']) && empty($this->settings['new']['confirmByAdmin']);
    }

    /**
     *
     * Returns true if admin confirmation is needed
     *
     * @param User $user
     * @return bool
     */
    protected function isAdminConfirmationMissing(User $user)
    {

        $needsAdminConfirmation = !empty($this->settings['new']['confirmByAdmin']) && !$user->getTxFemanagerConfirmedbyadmin();

        // auto approvement
        if ($this->settings['new']['autoApprovement']['enable'] == 1) {
            if ($this->approveUserAutomatically($user)) {
                $needsAdminConfirmation = false;
                $this->signalSlotDispatcher->dispatch(
                    __CLASS__,
                    __FUNCTION__ . 'AutoApprovementDone',
                    [$user, $this]
                );
            } else {
                $needsAdminConfirmation = true;
                $this->signalSlotDispatcher->dispatch(
                    __CLASS__,
                    __FUNCTION__ . 'AutoApprovementRefused',
                    [$user, $this]
                );
            };
        }

        return $needsAdminConfirmation;
    }


    /**
     *  checks if the user belongs to a whitlisted domain
     *
     * @param User $user
     *
     * @return bool true, if domain is whitelisted
     */
    protected function approveUserAutomatically($user)
    {
        // get configuration
        $whitelistDomainsAllowed = GeneralUtility::trimExplode(',',
            $this->settings['new']['autoApprovement']['whitelistDomains']['allowTopLevelDomains']);

        $whitelistDomainsExceptions = GeneralUtility::trimExplode(',',
            $this->settings['new']['autoApprovement']['whitelistDomains']['exceptions']);

        // could be used for automatically denyments
        // @todo add feature
        #$blacklistDomains = t3lib_div::trimExplode(',', $this->settings['autoApprovement']['blacklistDomains']);

        // get domain from user

        $host_names = explode(".", $user->getEmailDomain());

        $userTopLevelDomain = '.' . end($host_names);

        // check if user domain is allowed
        if (in_array($userTopLevelDomain, $whitelistDomainsAllowed)) {
            // check if the domain has a restriction

            if (in_array($user->getEmailDomain(), $whitelistDomainsExceptions)) {
                return false;
            }
            if ($this->doApprovement($user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param User $user
     * @param string $status
     * @return mixed
     */
    protected function doApprovement($user, $status = '')
    {
        $user = FrontendUtility::forceValues($user, $this->config['new.']['forceValues.']['onAdminConfirmation.']);
        $user->setTxFemanagerConfirmedbyadmin(true);
        $user->setDisable(false);
        $this->userRepository->update($user);
        $this->addFlashMessage(LocalizationUtility::translate('create'));
        LogUtility::log(Log::STATUS_REGISTRATIONCONFIRMEDADMIN, $user);
        $this->finalCreate($user, 'new', 'createStatus', false, $status = '', false);

        return true;
    }

}


