<div id="content">
    @switch($link)
        @case('report-history')
            <livewire:report-history />
        @break

        @case('all-reports')
            <livewire:all-reports />
        @break

        @case('create-project')
            <livewire:create-project />
        @break

        @case('personal-reports')
            <livewire:personal-reports/>
        @break

        @case('subscription')
            <livewire:subscription />
        @break

        @case('daily-log')
            <livewire:daily-log />
        @break
        
        @case('daily-form')

        <livewire:daily-form {{-- 之後移植至原本組件 --}}
            :id="$data['id']"
            :project="$data['project_category']['category']"
            :category="$data['type']"
            :content="$data['description']"
            :time="$data['use_time']"
            :progress="$data['progress']"
            :date="date('Y-m-d', $data['report_date'])" 
        />
        @break

        @case('user-report')
        <livewire:user-report
            :id="$data['id']"
        />
        @break

        @case('project-report')
        <livewire:project-report
            :id="$data['id']"
        />
        @break

        @case('department-report')
        <livewire:department-report
            :department="$data['department']"
        />
        @break

        @default
            <livewire:daily-log />
    @endswitch
    <br><br><br><br><br>
</div>
