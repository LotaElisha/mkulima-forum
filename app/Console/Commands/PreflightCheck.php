<?php

namespace App\Console\Commands;

use App\Services\System\ProductionReadiness;
use Illuminate\Console\Command;

/**
 * One command that answers "is this deployable?".
 *
 * The checks themselves live in ProductionReadiness, shared with the admin
 * readiness screen, so the terminal and the dashboard can never disagree about
 * what is wrong.
 *
 * Exits non-zero on anything blocking, so it can gate a deploy script:
 *   php artisan mkulima:preflight && ./deploy.sh
 */
class PreflightCheck extends Command
{
    protected $signature = 'mkulima:preflight {--strict : Treat warnings as failures}';

    protected $description = 'Verify this environment is ready to serve production traffic';

    public function handle(ProductionReadiness $readiness): int
    {
        $result = $readiness->run();

        $this->newLine();
        $this->line('  <fg=green;options=bold>Mkulima Forum — pre-flight</>');
        $this->line('  <fg=gray>'.config('app.url').' · '.config('app.env').'</>');
        $this->newLine();

        $currentGroup = null;
        foreach ($result['checks'] as $check) {
            if ($check['group'] !== $currentGroup) {
                $currentGroup = $check['group'];
                $this->line("  <options=bold>{$currentGroup}</>");
            }

            [$mark, $colour] = match ($check['status']) {
                ProductionReadiness::OK => ['✓', 'green'],
                ProductionReadiness::WARN => ['!', 'yellow'],
                default => ['✗', 'red'],
            };

            $detail = $check['detail'] ? " <fg=gray>({$check['detail']})</>" : '';
            $this->line("    <fg={$colour}>{$mark}</> {$check['name']}{$detail}");
        }

        $this->newLine();

        foreach ($result['checks'] as $check) {
            if ($check['status'] === ProductionReadiness::OK) {
                continue;
            }
            $label = $check['blocking'] ? '<fg=red;options=bold>BLOCKING</>' : '<fg=yellow;options=bold>WARNING </>';
            $this->line("  {$label} {$check['name']}");
            if ($check['consequence']) {
                $this->line("           <fg=gray>{$check['consequence']}</>");
            }
        }

        $this->newLine();
        $this->line(sprintf(
            '  %d passed, %d warning(s), %d blocking',
            $result['summary']['ok'],
            $result['summary']['warn'],
            $result['summary']['fail'],
        ));
        $this->newLine();

        if (! $result['ready']) {
            return self::FAILURE;
        }

        return ($this->option('strict') && $result['summary']['warn'] > 0) ? self::FAILURE : self::SUCCESS;
    }
}
