<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
call_user_func(function () {

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'In2code.femanager',
        'Pi1',
        [
            'User' => 'list, show, fileUpload, fileDelete, validate, loginAs, approveUser, declineUser',
            'New' => 'create, new, confirmCreateRequest, createStatus',
            'Edit' => 'edit, update, delete, confirmUpdateRequest',
            'Invitation' => 'new, create, edit, update, delete, status',
        ],
        [
            'User' => 'list, fileUpload, fileDelete, validate, loginAs, approveUser, declineUser',
            'New' => 'create, new, confirmCreateRequest, createStatus',
            'Edit' => 'edit, update, delete, confirmUpdateRequest',
            'Invitation' => 'new, create, edit, update, delete',
        ]
    );

    // eID for Field Validation (FE)
    $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['femanagerValidate'] = 'EXT:femanager/Classes/Eid/ValidateEid.php';

    // eID for FeUser simulation (FE)
    $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['femanagerLoginAs'] = 'EXT:femanager/Classes/Eid/LoginAsEid.php';
});
