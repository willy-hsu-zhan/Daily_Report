<?php

namespace App\Console\Commands\Restore;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\MailRelationship;
use App\Models\ProjectMailRelationship;
use App\Models\ProjectCategory;
use Exception;

class FixRelationShip extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:fix-relation-ship';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fix relationShip - project_category_id and relation_user_id';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userIds = User::all()->pluck('id')->toArray();

        $projectCategoryIds = ProjectCategory::all()->pluck('id')->toArray();

        $mailRelationShips = MailRelationship::all();

        $projectMailRelationShips = ProjectMailRelationship::all();

        foreach( $mailRelationShips as $mailRelationShip )
        {
            if( empty($mailRelationShip->relation_user_id) )
            {
                continue;
            }

            $relationUserIds = str_contains($mailRelationShip->relation_user_id, ',') ? explode(',', $mailRelationShip->relation_user_id) : [$mailRelationShip->relation_user_id];

            $difference = array_diff($relationUserIds, $userIds);

            if( !empty($difference) )
            {
                $relationUserIds = array_values(array_diff($relationUserIds, $difference)); // array

                $relationUserIds = implode(",", $relationUserIds); // string

                $mailRelationShip->relation_user_id = $relationUserIds;

                $mailRelationShip->save();

                $this->info('user_id : ' . $mailRelationShip->user_id . ' relation user_ids has been fixed by romove none user_id : ' . implode(',', $difference));
            }
            else
            {
                continue;
            }
        }

        foreach( $projectMailRelationShips as $projectMailRelationShip )
        {
            
            if( empty($projectMailRelationShip->relation_project_category_id) )
            {
                continue;
            }

            $relationProjectCategoryIds = str_contains($projectMailRelationShip->relation_project_category_id, ',') ? 
                                            explode(',', $projectMailRelationShip->relation_project_category_id) : [$projectMailRelationShip->relation_project_category_id];

            $difference = array_diff($relationProjectCategoryIds, $projectCategoryIds);

            if( !empty($difference) )
            {
                $relationProjectCategoryIds = array_values(array_diff($relationProjectCategoryIds, $difference)); // array

                $relationProjectCategoryIds = implode(",", $relationProjectCategoryIds); // string

                $projectMailRelationShip->relation_project_category_id = $relationProjectCategoryIds;

                $projectMailRelationShip->save();

                $this->info('user_id : ' . $projectMailRelationShip->user_id . ' relation project_category_id has been fixed by romove none project_category_id : ' . implode(',', $difference));
            }
            else
            {
                continue;
            }
        }
    }
}
