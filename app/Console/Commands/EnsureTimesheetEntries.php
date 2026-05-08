<?php

namespace App\Console\Commands;

use App\Models\Timesheet;
use App\Models\TimesheetEntry;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('timesheets:ensure-entries')]
#[Description('Ensure all timesheets have entry lines for each day in their defined period')]
class EnsureTimesheetEntries extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking all timesheets for missing entries...');

        $timesheets = Timesheet::all();
        $totalFixed = 0;
        $totalEntriesCreated = 0;

        foreach ($timesheets as $timesheet) {
            $this->line("Checking timesheet ID {$timesheet->id} for employee {$timesheet->employee->first_name} {$timesheet->employee->last_name}");

            $existingEntries = $timesheet->entries()->pluck('date')->map(fn($date) => $date->toDateString())->toArray();
            $expectedDates = [];

            $start = Carbon::parse($timesheet->period_start);
            $end = Carbon::parse($timesheet->period_end);

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $expectedDates[] = $date->toDateString();
            }

            $missingDates = array_diff($expectedDates, $existingEntries);

            if (!empty($missingDates)) {
                $this->warn("  Missing entries for dates: " . implode(', ', $missingDates));

                foreach ($missingDates as $missingDate) {
                    TimesheetEntry::create([
                        'timesheet_id' => $timesheet->id,
                        'date' => $missingDate,
                        'planned_hours' => 0,
                    ]);
                    $totalEntriesCreated++;
                }

                $totalFixed++;
                $this->info("  Created " . count($missingDates) . " missing entries");
            } else {
                $this->info("  All entries present ✓");
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("- Timesheets checked: {$timesheets->count()}");
        $this->info("- Timesheets fixed: {$totalFixed}");
        $this->info("- Total entries created: {$totalEntriesCreated}");

        if ($totalFixed > 0) {
            $this->info('All timesheets now have complete entry lines!');
        } else {
            $this->info('All timesheets already have complete entries.');
        }
    }
}
