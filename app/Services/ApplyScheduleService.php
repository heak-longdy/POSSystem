<?php

namespace App\Services;

use App\Models\FormQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\UploadFile;

class ApplyScheduleService
{
    public function getDataFormQueues()
    {
        return FormQueue::where('status', 1)->orderBy('id', 'asc')->get();
    }

    public function SendMail($view, $to, $subject = "", $data = "", $attachmentPath = [])
    {
        try {
            Mail::send($view, $data, function ($message) use ($to, $subject, $attachmentPath) {
                $message->to($to)->subject($subject);
                if ($attachmentPath) {
                    foreach ($attachmentPath as $file) {
                        if (file_exists($file)) {
                            $message->attach($file);
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            Log::channel('single')->error('Error sending email: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function GetAttachmentPath($files = [])
    {
        $dataFiles = [];
        if (count($files) > 0) {
            foreach ($files as $file) {
                $itemFile = public_path('uploads/ApplyFormRequest/' . $file);
                $dataFiles[] = $itemFile;
            }
        }
        return $dataFiles;
    }

    public function ApplyForm()
    {
        DB::beginTransaction();
        try {
            $data = $this->getDataFormQueues();
            foreach ($data as $item) {
                $dataJson = $item->dataJson ? json_decode($item->dataJson) : "";
                $attachmentPath = $item->files ? $this->GetAttachmentPath(json_decode($item->files)) : [];
                $to = $item->mailTo;
                $obj = [
                    "id" => $dataJson?->job_id ?? "",
                    "email" => $dataJson->email ?? "",
                    "subject" => $dataJson?->subject ?? "",
                    "job_id" =>  $dataJson?->job_id ?? "",
                    "job_title" =>  $dataJson?->job_title ?? "",
                    "phone" =>  $dataJson?->phone ?? "",
                    "name" =>  $dataJson?->name ?? "",
                    "description" =>  $dataJson?->description ?? "",
                ];

                if ($item->type == "Contact") {
                    $subject = $dataJson?->subject ?? "Contact by " . $dataJson->name;
                    $view = "website::pages.mailContact";
                } else {
                    $subject = $dataJson?->job_title . " " . "(" . $dataJson->job_id . ")" . " application from " . $dataJson->name;
                    $view = "website::pages.mailApplyJob";
                }

                $this->SendMail($view, $to, $subject, $obj, $attachmentPath);
                $item->status = 2;
                $item->update();
                // Log::channel('single')->info('Data from FormQueue: ', ['data' => $obj]);
            }
            // Log::channel('single')->info('Email sent.');
            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            Log::channel('single')->error('Error applying form: ' . $e->getMessage());
            return $e->getMessage();
        }
    }
    public function DeleteFormQueueAfterEachFileSendMailComplete()
    {
        $dataLists =  FormQueue::where('status', '!=', 1)->get();
        DB::beginTransaction();
        try {
            foreach ($dataLists as $item) {

                $files = $item?->files ? json_decode($item?->files) : [];
                if (count($files) > 0) {
                    foreach ($files as $file) {
                        UploadFile::deleteFile('/ApplyFormRequest', $file);
                    }
                }
                $item->delete();
            }
            DB::commit();
            return "success";
        } catch (\Exception $e) {
            DB::rollback();
            Log::channel('single')->error('Error DeleteFormQueueAfterEachFileSendMailComplete: ' . $e->getMessage());
            return $e->getMessage();
        }
    }
}
