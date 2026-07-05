<?php

namespace App\Http\Controllers\website;

use App\Http\Controllers\BaseWebSiteController;
use App\Http\Requests\Web\ContactRequest;
use App\Mail\sendContact;
use Illuminate\Support\Facades\Mail;
use App\Models\FormQueue;

class ContactController extends BaseWebSiteController
{
    protected $layout = 'website::pages.';
    public function contact()
    {
        return view($this->layout . 'contact');
    }
    public function contactSend(ContactRequest $req)
    {
        $item = collect($req->all());
        try {
            $mailTo = collect(config('mailSend'))->get('contact');
            $this->storeQueue($item, [], $mailTo);
            return response()->json([
                'message' => 'success',
                'url'   => url()->previous()
            ]);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json(['message' => 'error', 'error' => 'Mail error: ' . $e->getMessage()]);
        }
    }
    public function storeQueue($item, $uploadedFiles, $mailTo, $type = "Contact")
    {
        $itemInsert = [
            "name" => $item->get('name'),
            "email" => $item->get('email'),
            "subject" => $item->get('subject'),
            "mailTo" => $mailTo,
            "phone" =>  $item->get('phone'),
            "description" =>  $item->get('description'),
            "status" => 1,
            "type" => $type,
            "data_files" => []
        ];
        $itemInsert['dataJson'] = json_encode($itemInsert);
        FormQueue::create($itemInsert);
    }
}
