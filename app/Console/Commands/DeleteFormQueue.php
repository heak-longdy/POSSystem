<?php

namespace App\Console\Commands;

use App\Services\ApplyScheduleService;
use Illuminate\Console\Command;

class DeleteFormQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deleteFormQueue:add';
    private $ApplyService;


    public function __construct(ApplyScheduleService $Apply)
    {
        parent::__construct();
        $this->ApplyService = $Apply;
    }

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
    public function handle()
    {
        $this->ApplyService->DeleteFormQueueAfterEachFileSendMailComplete();
        return Command::SUCCESS;
    }
}
