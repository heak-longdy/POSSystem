<?php

namespace App\Http\Controllers\website;

use App\Http\Controllers\BaseWebSiteController;
use App\Http\Requests\Web\ApplyFormRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\JobApplicationReceived;
use App\Models\FormQueue;
use App\Models\Job;
use App\Services\ApplyScheduleService;
use App\Models\UploadFile;
use Illuminate\Support\Facades\DB;

class ApplyFormController extends BaseWebSiteController
{
    protected $layouts = 'website::pages.';
    private $ApplyService;
    public function __construct(ApplyScheduleService $Apply)
    {
        parent::__construct();
        $this->ApplyService = $Apply;
    }
    public function applyForm($id = null)
    {
        // Check if the job ID is provided
        if (empty($id)) {
            return redirect()->back();
        }

        // Attempt to find the job by ID
        $job = Job::find($id);

        // Check if the job exists
        if (!$job) {
            return redirect()->back();
        }

        // Return the apply form view with the job data
        return view($this->layouts . 'applyForm', ['job' => $job]);
    }
    public function apply(ApplyFormRequest $req)
    {
        DB::beginTransaction();
        try {
            $item = collect($req->all());
            $mailTo = collect(config('mailSend'))->get('applyJob');
            $uploadedFiles = $req->file('files');
            $this->storeQueue($item, $uploadedFiles, $mailTo);
            // if ($item->get('is_send') == "active") {
            //     Mail::to($mailTo)->send(new JobApplicationReceived($item));
            // } else {
            //     $this->storeQueue($item, $uploadedFiles, $mailTo);
            // }
            // dd($item);
            DB::commit();
            return response()->json([
                'message' => 'success',
                'url'   => url()->previous()
            ]);
        } catch (\Exception $e) {
            // Catch any other exceptions
            DB::rollback();
            return response()->json(['message' => 'error', 'error' => 'Mail error: ' . $e->getMessage()]);
        }
    }

    public function storeQueue($item, $uploadedFiles, $mailTo, $type = "ApplyJob")
    {
        $uniqueNameFiles = [];
        $originalNameFiles = [];
        foreach ($uploadedFiles as $file) {
            $fileItem = UploadFile::saveFile('/ApplyFormRequest', $file, '');
            $dataFile[] = $fileItem;
            $uniqueNameFiles[] = $fileItem->get('uniqueName');
            $originalNameFiles[] = $fileItem->get('originalName');
        }
        $item['data_files'] = $dataFile;
        $itemInsert = [
            "job_id" => $item->get('job_id'),
            "job_title" => $item->get('job_title'),
            "name" => $item->get('name'),
            "email" => $item->get('email'),
            "mailTo" => $mailTo,
            "files" => json_encode($uniqueNameFiles),
            "phone" =>  $item->get('phone'),
            "description" =>  $item->get('description'),
            "status" => 1,
            "type" => $type,
            "data_files" => $dataFile
        ];
        $itemInsert['dataJson'] = json_encode($itemInsert);
        FormQueue::create($itemInsert);
    }
}
