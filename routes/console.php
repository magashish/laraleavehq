<?php

use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('import:leave-requests {file}', function (string $file) {
    if (!file_exists($file)) {
        $this->error("File not found: {$file}");
        return 1;
    }

    $handle = fopen($file, 'r');
    $header = array_map('trim', fgetcsv($handle)); // skip header row

    $inserted = 0;
    $skipped  = 0;
    $errors   = [];
    $row      = 1;

    while (($data = fgetcsv($handle)) !== false) {
        $row++;
        $record = array_combine($header, array_map('trim', $data));

        // Resolve employee
        $employee = User::find($record['employee_id'] ?? null);
        if (!$employee) {
            $errors[] = "Row {$row}: employee_id '{$record['employee_id']}' not found — skipped.";
            $skipped++;
            continue;
        }

        // Resolve leave type (optional)
        $leaveTypeId = null;
        if (!empty($record['leave_type_id'])) {
            $lt = LeaveType::find($record['leave_type_id']);
            if (!$lt) {
                $errors[] = "Row {$row}: leave_type_id '{$record['leave_type_id']}' not found — skipped.";
                $skipped++;
                continue;
            }
            $leaveTypeId = $lt->id;
        }

        // Validate dates
        try {
            $startDate = Carbon::parse($record['start_date'])->toDateString();
            $endDate   = Carbon::parse($record['end_date'] ?? $record['start_date'])->toDateString();
        } catch (\Exception $e) {
            $errors[] = "Row {$row}: invalid date — skipped.";
            $skipped++;
            continue;
        }

        $isHalfDay   = in_array(strtolower($record['is_half_day'] ?? ''), ['1', 'yes', 'true']);
        $halfDayPart = $isHalfDay ? (strtolower($record['half_day_part'] ?? 'morning')) : null;
        $days        = isset($record['days']) && is_numeric($record['days']) ? (float) $record['days'] : ($isHalfDay ? 0.5 : 1);
        $status      = in_array($record['status'] ?? '', ['approved', 'pending', 'rejected']) ? $record['status'] : 'approved';

        // Find who approved (optional column)
        $approvedById = null;
        if (!empty($record['approved_by_id'])) {
            $approvedById = User::find($record['approved_by_id'])?->id;
        }

        LeaveRequest::create([
            'employee_id'    => $employee->id,
            'leave_type_id'  => $leaveTypeId,
            'start_date'     => $startDate,
            'end_date'       => $isHalfDay ? $startDate : $endDate,
            'days'           => $days,
            'is_half_day'    => $isHalfDay,
            'half_day_part'  => $halfDayPart,
            'reason'         => $record['reason'] ?? '',
            'status'         => $status,
            'approved_by_id' => $approvedById,
            'approved_at'    => $status === 'approved' ? now() : null,
            'admin_override' => false,
        ]);

        $inserted++;
    }

    fclose($handle);

    $this->info("Done — {$inserted} imported, {$skipped} skipped.");
    foreach ($errors as $e) {
        $this->warn($e);
    }
})->purpose('Import leave requests from a CSV file');

