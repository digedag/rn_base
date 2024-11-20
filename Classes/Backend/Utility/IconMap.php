<?php

namespace Sys25\RnBase\Backend\Utility;

use Sys25\RnBase\Utility\TYPO3;

/**
 * Mapping for pre 10.x icons.
 */
class IconMap
{
    private static $aliasMap = [
        'actions-view-go-back' => 'actions-arrow-down-left',
        'actions-view-go-forward' => 'actions-arrow-down-right',
        'actions-version-workspace-sendtoprevstage' => 'actions-arrow-left',
        'actions-view-go-down' => 'actions-arrow-right-down',
        'actions-view-go-up' => 'actions-arrow-right-up',
        'actions-version-workspace-sendtostage' => 'actions-arrow-right',
        'actions-system-cache-clear' => 'actions-bolt',
        'actions-system-cache-clear-impact-high' => 'actions-bolt',
        'actions-system-cache-clear-impact-medium' => 'actions-bolt',
        'actions-system-cache-clear-impact-low' => 'actions-bolt',
        'actions-system-cache-clear-rte' => 'actions-bolt',
        'information-os-apple' => 'actions-brand-apple',
        'information-git' => 'actions-brand-git',
        'information-os-linux' => 'actions-brand-linux',
        'information-php-version' => 'actions-brand-php',
        'actions-slack' => 'actions-brand-slack',
        'actions-typo3' => 'actions-brand-typo3',
        'information-os-windows' => 'actions-brand-windows',
        'actions-brand-twitter' => 'actions-brand-x',
        'actions-edit-pick-date' => 'actions-calendar-alternative',
        'actions-move-to-bottom' => 'actions-caret-bar-bottom',
        'actions-move-to-top' => 'actions-caret-bar-top',
        'actions-move-down' => 'actions-caret-down',
        'actions-pagetree-expand' => 'actions-caret-down',
        'status-status-sorting-desc' => 'actions-caret-down',
        'status-status-sorting-light-desc' => 'actions-caret-down',
        'apps-irre-expanded' => 'actions-caret-down',
        'apps-pagetree-expand' => 'actions-caret-down',
        'actions-move-left' => 'actions-caret-left',
        'actions-move-right' => 'actions-caret-right',
        'actions-pagetree-collapse' => 'actions-caret-right',
        'status-status-current' => 'actions-caret-right',
        'apps-irre-collapsed' => 'actions-caret-right',
        'apps-pagetree-collapse' => 'actions-caret-right',
        'actions-move-up' => 'actions-caret-up',
        'status-status-sorting-asc' => 'actions-caret-up',
        'status-status-sorting-light-asc' => 'actions-caret-up',
        'status-dialog-ok' => 'actions-check-circle',
        'actions-check-unmarkstate' => 'actions-check-square',
        'apps-pagetree-category-toggle-hide-checked' => 'actions-check-square',
        'sysnote-type-4' => 'actions-check-square',
        'status-status-checked' => 'actions-check',
        'status-status-permission-granted' => 'actions-check',
        'actions-view-paging-first' => 'actions-chevron-bar-left',
        'actions-view-paging-first-disabled' => 'actions-chevron-bar-left',
        'actions-view-paging-last' => 'actions-chevron-bar-right',
        'actions-view-paging-last-disabled' => 'actions-chevron-bar-right',
        'actions-view-paging-previous' => 'actions-chevron-double-left',
        'actions-view-paging-previous-disabled' => 'actions-chevron-double-left',
        'actions-view-paging-next' => 'actions-chevron-double-right',
        'actions-view-paging-next-disabled' => 'actions-chevron-double-right',
        'actions-view-list-expand' => 'actions-chevron-down',
        'actions-view-table-collapse' => 'actions-chevron-left',
        'actions-view-table-expand' => 'actions-chevron-right',
        'actions-view-list-collapse' => 'actions-chevron-up',
        'actions-edit-copy-release' => 'actions-clipboard-close',
        'actions-document-paste-into' => 'actions-clipboard-paste',
        'actions-document-paste' => 'actions-clipboard-paste',
        'actions-edit-copy' => 'actions-clipboard',
        'actions-message-error-close' => 'actions-close',
        'actions-message-information-close' => 'actions-close',
        'actions-message-notice-close' => 'actions-close',
        'actions-message-ok-close' => 'actions-close',
        'actions-message-warning-close' => 'actions-close',
        'actions-input-clear' => 'actions-close',
        'status-status-permission-denied' => 'actions-close',
        'actions-online-media-add' => 'actions-cloud',
        'actions-edit-merge-localization' => 'actions-code-merge-localization',
        'actions-merge' => 'actions-code-merge',
        'actions-version-document-remove' => 'actions-code-pull-request-close',
        'sysnote-type-2' => 'actions-code',
        'actions-system-extension-configure' => 'actions-cog-alt',
        'sysnote-type-1' => 'actions-cog',
        'actions-edit-cut-release' => 'actions-cut-release',
        'actions-edit-cut' => 'actions-cut',
        'actions-system-extension-sqldump' => 'actions-database-export',
        'information-database' => 'actions-database',
        'information-debugger' => 'actions-debug',
        'actions-edit-undelete-edit' => 'actions-delete-edit',
        'actions-edit-restore' => 'actions-delete-restore',
        'actions-edit-delete' => 'actions-delete',
        'actions-selection-delete' => 'actions-delete',
        'actions-dice-one' => 'actions-dice-1',
        'actions-dice-two' => 'actions-dice-2',
        'actions-dice-three' => 'actions-dice-3',
        'actions-dice-four' => 'actions-dice-4',
        'actions-dice-five' => 'actions-dice-5',
        'actions-dice-six' => 'actions-dice-6',
        'actions-document-new' => 'actions-document-add',
        'actions-document-open' => 'actions-document-edit',
        'actions-document-open-read-only' => 'actions-document-readonly',
        'actions-insert-reference' => 'actions-document-share',
        'actions-system-extension-download' => 'actions-download',
        'actions-edit-download' => 'actions-download',
        'actions-move-move' => 'actions-drag',
        'actions-document-duplicates-select' => 'actions-duplicates',
        'status-dialog-information' => 'actions-exclamation-circle',
        'status-dialog-notification' => 'actions-exclamation-circle',
        'status-dialog-warning' => 'actions-exclamation-triangle',
        'status-dialog-error' => 'actions-exclamation-triangle',
        'actions-system-extension-install' => 'actions-extension-add',
        'actions-system-extension-import' => 'actions-extension-import',
        'actions-system-extension-update' => 'actions-extension-refresh',
        'actions-system-extension-update-disable' => 'actions-extension-refresh',
        'actions-system-extension-uninstall' => 'actions-extension-remove',
        'actions-version-workspaces-preview-link' => 'actions-eye-link',
        'actions-version-workspace-preview' => 'actions-eye',
        'actions-view' => 'actions-eye',
        'actions-page-new' => 'actions-file-add',
        'actions-document-export-csv' => 'actions-file-csv-download',
        'actions-version-page-open' => 'actions-file-edit',
        'actions-page-open' => 'actions-file-edit',
        'actions-page-move' => 'actions-file-move',
        'actions-system-pagemodule-open' => 'actions-file-search',
        'actions-document-export-t3d' => 'actions-file-t3d-download',
        'actions-document-import-t3d' => 'actions-file-t3d-upload',
        'actions-view-page' => 'actions-file-view',
        'actions-system-tree-search-open' => 'actions-filter',
        'actions-insert-record' => 'actions-folder',
        'actions-document-history-open' => 'actions-history',
        'actions-document-info' => 'actions-info',
        'actions-edit-insert-default' => 'actions-insert',
        'actions-wizard-rte' => 'actions-link',
        'actions-wizard-link' => 'actions-link',
        'actions-system-list-open' => 'actions-list-alternative',
        'actions-sign-in' => 'actions-login',
        'actions-sign-out' => 'actions-logout',
        'actions-edit-localize-status-high' => 'actions-message-add',
        'actions-localize' => 'actions-message-localize',
        'actions-edit-localize-status-low' => 'actions-message-remove',
        'actions-remove' => 'actions-minus',
        'information-composer-mode' => 'actions-music',
        'sysnote-type-0' => 'actions-note',
        'actions-system-typoscript-documentation' => 'actions-notebook-typoscript',
        'actions-system-typoscript-documentation-open' => 'actions-notebook-typoscript',
        'actions-system-extension-documentation' => 'actions-notebook',
        'actions-pencil' => 'actions-open',
        'actions-system-options-view' => 'actions-options',
        'actions-pagetree-mountroot' => 'actions-pagetree-mount',
        'actions-document-paste-after' => 'actions-paste-after',
        'actions-add-placeholder' => 'actions-placeholder-add',
        'actions-add' => 'actions-plus',
        'actions-system-help-open' => 'actions-question',
        'actions-edit-redo' => 'actions-redo',
        'actions-system-refresh' => 'actions-refresh',
        'actions-edit-rename' => 'actions-rename',
        'actions-edit-replace' => 'actions-replace',
        'actions-document-save-new' => 'actions-save-add',
        'actions-document-save-close' => 'actions-save-close',
        'actions-document-save-cleartranslationcache' => 'actions-save-translation-clearcache',
        'actions-document-save-translation' => 'actions-save-translation',
        'actions-document-save-view' => 'actions-save-view',
        'actions-document-save' => 'actions-save',
        'information-webserver' => 'actions-server',
        'share-alt' => 'actions-share-alt',
        'actions-check-markstate' => 'actions-square',
        'actions-system-shortcut-active' => 'actions-star',
        'actions-system-shortcut-new' => 'actions-star',
        'actions-version-swap-version' => 'actions-swap',
        'actions-version-swap-workspace' => 'actions-swap',
        'sysnote-type-3' => 'actions-thumbtack',
        'actions-edit-unhide' => 'actions-toggle-off',
        'actions-edit-hide' => 'actions-toggle-on',
        'actions-edit-undo' => 'actions-undo',
        'actions-edit-upload' => 'actions-upload',
        'actions-system-backend-user-emulate' => 'actions-user-emulate',
        'actions-system-backend-user-switch' => 'actions-user-switch',
        'actions-variable-select' => 'actions-variable-add',
        'information-application-context' => 'actions-window-cog',
        'empty-empty' => 'miscellaneous-placeholder',
        'module-templates' => 'module-template',
        'module-tstemplate' => 'module-tsconfig',
        'spinner-circle-light' => 'spinner-circle',
        'spinner-circle-dark' => 'spinner-circle',
    ];

    private static $flippedAliasMap;

    /**
     * Wandelt in T3 10 und höher alte Icon-Bezeichner in die entsprechenden neuen Bezeichner um.
     * Für ältere T3 Versionen werden die neuen Bezeichner, sofern vorhanden, in die entsprechenden
     * alten Icons umgewandelt.
     *
     * @see https://typo3.github.io/TYPO3.Icons/index.html
     *
     * @param string|null $identifier
     * @return mixed
     */
    public static function alias(?string $identifier)
    {
        if (TYPO3::isTYPO104OrHigher()) {
            return self::$aliasMap[$identifier] ?? $identifier;
        }

        if (empty(self::$flippedAliasMap)) {
            self::$flippedAliasMap = array_flip(self::$aliasMap);
        }

        return self::$flippedAliasMap[$identifier] ?? $identifier;
    }
}