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
    protected $signature = 'auth:users:reverse-display-name {--batch-size=500} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
    protected $description = 'Replace "LastName, FirstName" display name with "FirstName LastName"';

    public function handle(): void
    {
        $dryRun = $this->option('dry-run');
        $batchSize = $this->option('batch-size');
        $offset = 0;
        $processed = 0;
        $updated = 0;

        while (true) {
            $users = \get_users([
                // All multisite users.
                'blog_id' => 0,
                'offset' => $offset,
                'number' => $batchSize,
            ]);

            if (!$users) {
                break;
            }

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

                $offset += $batchSize;
            }
        }

        $this->info("Complete. Processed {$processed} users, updated {$updated}.");
    }
}
