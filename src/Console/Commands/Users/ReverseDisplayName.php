<?php

declare(strict_types=1);

namespace Kleinweb\Auth\Console\Commands\Users;

use Illuminate\Console\Command;

final class ReverseDisplayName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:users:reverse-display-name {--batch-size=500} {--dry-run} {--verbose}';

    /**
     * The console command description.
     *
     * @var string
     */
    // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
    protected $description = 'Replace "LastName, FirstName" display name with "FirstName LastName"';

    public function handle(): void
    {
        global $wpdb;

        $dryRun = $this->option('dry-run');
        $batchSize = $this->option('batch-size');
        $verbose = $this->option('verbose');

        $offset = 0;
        $processed = 0;
        $updated = 0;

        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");

        $this->info("Found {$total} total users to process");

        $iterations = 0;
        while ($offset < $total) {
            $iterations += 1;

            if ($verbose) {
                $this->info("--- Iteration {$iterations} ---");
                $this->info("Current offset: {$offset}");
                $this->info("Batch size: {$batchSize}");
                $this->info("Total: {$total}");
                $this->info("Condition check: offset ({$offset}) < total ({$total})? " . ($offset < $total ? 'yes' : 'no'));
            }

            $ids = $wpdb->get_col($wpdb->prepare(
                "SELECT ID FROM {$wpdb->users} ORDER BY ID LIMIT %d OFFSET %d",
                $batchSize, $offset,
            ));

            if ($verbose) {
                $this->info("Retrieved " . count($ids) . " user IDs from database");
            }

            if (!$ids) {
                if ($verbose) {
                    $this->info('No user IDs returned. Quitting.');
                }
                break;
            }

            $users = get_users(['include' => $ids]);

            foreach ($users as $user) {
                $displayName = $user->display_name;

                if (preg_match('/^([^,]+),\s*(.*)$/', $displayName, $matches)) {
                    $lastName = trim($matches[1]);
                    $firstName = trim($matches[2]);
                    $newDisplayName = $firstName . ' ' . $lastName;

                    $this->line(sprintf(
                        'User %d: Replacing display name "%s" with "%s"',
                        $user->ID,
                        $newDisplayName,
                        $displayName,
                    ));

                    if (!$dryRun) {
                        wp_update_user([
                            'ID' => $user->id,
                            'display_name' => $newDisplayName,
                        ]);
                    }

                    $updated += 1;
                }

                $processed += 1;

                $this->info("Processed {$processed} users, updated {$updated}...");

                if ($verbose) {
                    $this->info("After processing: processed={$processed}, updated={$updated}");
                    $this->info("About to increment offset from {$offset} to " . ($offset + $batchSize));
                }

                $offset += $batchSize;

                if ($verbose) {
                    $this->info("New offset: {$offset}");
                }
            }
        }

        if ($verbose) {
            $this->info("Loop ended. Reason: offset ({$offset}) >= total ({$total})");
        }

        $this->info("Complete. Processed {$processed} users, updated {$updated}.");
    }
}
