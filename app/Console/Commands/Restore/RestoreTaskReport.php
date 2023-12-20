<?php

namespace App\Console\Commands\Restore;

use Illuminate\Console\Command;
use App\Models\TaskReport;
use App\Models\ProjectCategory;
use App\Models\User;

class RestoreTaskReport extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:task_report {user_id} {project_category_id} {report_date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore a soft-deleted task report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId            = $this->argument('user_id');
        $projectCategoryId = $this->argument('project_category_id');
        $reportDate        = $this->argument('report_date');

        $startTimeStamp = strtotime($reportDate . ' 00:00:00');
        $endTimeStamp   = strtotime($reportDate . ' 23:59:59');

        $userExists = User::where('id', $userId)->exists();
        if( !$userExists )
        {
            $this->error('User with the provided ID does not exist.');
            return;
        }

        // 驗證 projectCategoryId 是否存在
        $projectCategoryExists = ProjectCategory::where('id', $projectCategoryId)->exists();
        if( !$projectCategoryExists )
        {
            $this->error('Project category with the provided ID does not exist.');
            return;
        }

        // 驗證 reportDate 是否符合 YYYY-MM-DD 格式
        $reportDateIsValid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $reportDate);
        if( !$reportDateIsValid )
        {
            $this->error('Invalid report date format. Please use YYYY-MM-DD.');
            return;
        }
        // 找到軟刪除的 TaskReport
        $taskReport = TaskReport::withTrashed()
            ->where('user_id', $userId)
            ->where('project_category_id', $projectCategoryId)
            ->whereBetween('report_date', [$startTimeStamp, $endTimeStamp])
            ->first();

        if( $taskReport )
        {
            // 恢復軟刪除的 TaskReport
            $taskReport->restore();
            $this->info('Task report restored successfully.');
        }
        else
        {
            $this->error('No soft-deleted task report found with the provided conditions.');
        }
    }
}
