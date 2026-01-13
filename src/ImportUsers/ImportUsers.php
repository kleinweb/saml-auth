<?php

// SPDX-FileCopyrightText: (C) 2025 Temple University <kleinweb@temple.edu>
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Kleinweb\Auth\ImportUsers;

use Args\wp_insert_user as wpInsertUserArgs;
use Kleinweb\Auth\Support\AdminNotice\AdminNotice;
use Kleinweb\Auth\Support\AdminNotice\AdminNoticeType;
use Kleinweb\Auth\Support\Org;
use Kleinweb\Lib\Hooks\Attributes\Action;
use Kleinweb\Lib\Hooks\Traits\Hookable;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Kleinweb\Lib\Support\CoreObjects;
use League\Csv\Reader;
use Illuminate\Http\UploadedFile;
use Webmozart\Assert\Assert;
use WP_Error;
use WP_User;

use function add_user_to_blog;
use function get_current_blog_id;
use function is_multisite;
use function is_user_member_of_blog;
use function is_wp_error;
use function wp_insert_user;

final class ImportUsers
{
    use Hookable;

    public const ACTION = 'kleinweb-auth-import-users';

    public const CAPABILITY = 'create_users';

    public function boot(): void
    {
        $this->registerHooks();
    }

    #[Action('admin_menu')]
    public function registerAdminPage(): void
    {
        add_users_page(
            page_title: __('Bulk Add Users', 'kleinweb-auth'),
            menu_title: __('Bulk Add Users', 'kleinweb-auth'),
            capability: self::CAPABILITY,
            menu_slug: 'import-users',
            callback: $this->renderAdminPage(...),
        );
    }

    public function renderAdminPage(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
            wp_die('You do not have sufficient permissions to access this page.');
        }

        $request = Request::instance();

        if ($request->input('_wpnonce')) {
            check_admin_referer(self::ACTION);

            $csvFile = $request->file('file');

            if (is_array($csvFile)) {
                $notice = new AdminNotice(
                    AdminNoticeType::ERROR,
                    'Somehow you were able to upload multiple files! Please do not.',
                );
            } elseif ($csvFile) {
                $results = $this->import($csvFile);

                if ($results->data) {
                    $notice = $results->errors
                        ? new AdminNotice(
                            AdminNoticeType::WARNING,
                            'Some users were imported successfully, but there were errors.',
                        )
                        : new AdminNotice(
                            AdminNoticeType::SUCCESS,
                            'Users import was successful',
                        );

                } else {
                    $notice = new AdminNotice(
                        AdminNoticeType::ERROR,
                        'No users were imported.',
                    );
                }

            } else {
                $notice = new AdminNotice(
                    AdminNoticeType::ERROR,
                    'Error during file upload',
                );
            }
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo \view('kleinweb-auth::admin.pages.import-users', [
            'notice' => $notice ?? null,
            'results' => $results ?? null,
        ]);
    }

    public function import(UploadedFile $file): Results
    {
        $results = new Results();
        $role = 'contributor';

        $csv = Reader::createFromPath($file->path(), open_mode: 'r');

        /*
         * Column-header string indexing requires unique string values
         * across all column headers.  Since we are processing a
         * user-provided CSV file, there is no way to make such a
         * guarantee (we have actually observed duplicate column names
         * in user-provided CSV files).  Because of that, we must drop
         * the header and lookup columns by their numeric index.
         *
         * @link https://github.com/thephpleague/csv/issues/380#issuecomment-589569092
         */
        $records
            = $csv->slice(offset: 1)
                ->select(0, 2)
                ->mapHeader(['display_name', 'username'])
                // Work around common Canvas CSV export oddities.
                ->filter(fn ($it) => ($it['display_name'] && $it['username']))
                ->filter(fn ($it) => ! Str::contains($it['display_name'], 'Points Possible'))
                ->getRecords();

        foreach ($records as $record) {
            ['display_name' => $displayName, 'username' => $username] = $record;

            // Canvas usually will format the display name as
            // `<LastName>, <FirstName>`.  This is not at all what we
            // want, so we normalize to `<FirstName> <LastName>` if a
            // comma is found in the parsed string.
            if (Str::contains($displayName, ', ')) {
                [$lastName, $firstName] = explode(', ', $displayName);
                $displayName = "{$firstName} {$lastName}";
            }

            if (!Org::isUid($username)) {
                $results->errors[] = new WP_Error(
                    'invalid_org_username',
                    sprintf('"%s" is not a valid organizational username', $username),
                );
                continue;
            }

            $inferredEmailAddress = Org::emailAddressify($username);
            $user
                = CoreObjects::getUserBy('login', $username)
                ?? CoreObjects::getUserBy('email', $inferredEmailAddress);

            if ($user) {
                $result = new Result($user, isExisting: true);

                /*
                 * Existing users on single-site WordPress instances
                 * should not have their roles changed.
                 */
                if (is_multisite()) {
                    $siteId = get_current_blog_id();

                    /*
                     * NOTE: This does not necessarily imply that the
                     * existing user has a *role* on the current site.
                     * The user is considered a "member" of a site
                     * when capabilities have been initialized for the
                     * user on that site, regardless of actual access
                     * levels granted by those capabilities.  While
                     * users *should* be assigned roles rather than
                     * primitive capabilities, that may not be the
                     * case in some edge cases.
                     *
                     * @see is_user_member_of_blog()
                     */
                    $hasSiteAccess = is_user_member_of_blog($user->ID, $siteId);

                    if ($hasSiteAccess) {
                        $result->hasAuthz = true;
                    } else {
                        add_user_to_blog($siteId, $user->ID, $role);
                    }
                } else {
                    $result->hasAuthz = true;
                }
            } else {
                $userArgs = new wpInsertUserArgs();
                $userArgs->user_login = $username;
                $userArgs->user_email = $inferredEmailAddress;
                $userArgs->user_pass = wp_generate_password();
                $userArgs->display_name = $displayName;
                $userArgs->nickname = $displayName;
                $userArgs->role = $role;

                $newUser = wp_insert_user($userArgs->toArray());

                if (is_wp_error($newUser)) {
                    $results->errors[] = $newUser;
                    continue;
                }

                $user = CoreObjects::getUser($newUser);
                Assert::isInstanceOf($user, WP_User::class);

                $result = new Result($user, isNew: true);
            }

            $results->data[] = $result;
        }

        return $results;

    }
}
