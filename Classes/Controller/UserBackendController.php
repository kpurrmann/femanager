<?php
declare(strict_types=1);
namespace In2code\Femanager\Controller;

use In2code\Femanager\Domain\Model\Log;
use In2code\Femanager\Domain\Model\User;
use In2code\Femanager\Utility\FrontendUtility;
use In2code\Femanager\Utility\LogUtility;
use In2code\Femanager\Utility\UserUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class UserBackendController
 */
class UserBackendController extends AbstractController
{

    /**
     * @param array $filter
     * @return void
     */
    public function listAction(array $filter = [])
    {
        $this->view->assignMultiple(
            [
                'users' => $this->userRepository->findAllInBackend($filter),
                'approveUsers' => $this->userRepository->findOpenApprovals(),
                'moduleUri' => BackendUtility::getModuleUrl('tce_db')
            ]
        );
    }

    /**
     * @param User $user
     * @return voidß
     */
    public function userLogoutAction(User $user)
    {
        UserUtility::removeFrontendSessionToUser($user);
        $this->addFlashMessage('User successfully logged out');
        $this->redirect('list');
    }

    /**
     * action approve User
     *
     * @param int $userID
     * @return void
     */
    public function approveUserAction($userID)
    {
        $user = $this->userRepository->findByUid($userID);

        $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ , [$user, $this]);
        $user = FrontendUtility::forceValues($user, $this->config['new.']['forceValues.']['onAdminConfirmation.']);
        $user->setTxFemanagerConfirmedbyadmin(true);
        $user->setDisable(false);
        LogUtility::log(Log::STATUS_REGISTRATIONCONFIRMEDADMIN, $user);
        $this->userRepository->update($user);

        $this->addFlashMessage($user->getName() . '  successfully approved');

        $this->finalCreate($user, 'list', 'createStatus', false, $status='', false);

        $this->redirect('list');
    }

    /**
     * action approve User
     *
     * @param int $userID
     * @return void
     */
    public function declineUserAction($userID)
    {
        $user = $this->userRepository->findByUid($userID);
        $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ , [$user, $this]);

        $this->userRepository->remove($user);

        $this->addFlashMessage($user->getName() . '  was declined');
        $this->redirect('list');
    }
    
}
