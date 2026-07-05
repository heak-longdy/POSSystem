<?php

namespace App\Console\Commands;

use App\Services\ApplyScheduleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ApplyJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applyJob:add';
    private $ApplyService;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function __construct(ApplyScheduleService $Apply)
    {
        parent::__construct();
        $this->ApplyService = $Apply;
        // $this->ApplyService->ApplyForm();
    }
    public function handle()
    {
        $this->ApplyService->ApplyForm();
        return Command::SUCCESS;
    }
}
