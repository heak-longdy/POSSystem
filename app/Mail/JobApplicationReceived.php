<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;

class JobApplicationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    private $files = null;
    private $mail = null;
    private $status = null;
    private $senderName = "fsfsfsfsfsfsfsfsf";
    public $uploadedFiles;
    public $subject = "";

    public function __construct($application)
    {
        $this->application = $application;
        $this->mail        = $application->get('email', '');
        $this->files        = $application->get('files', []);
        $this->uploadedFiles = $application->get('files', []);
        $this->uploadedFiles = $application->get('files', []);
    }
    public function build()
    {
        $JobTitle = explode('*', $this->application['job_title']);
        // Trim the parts to remove any extra spaces
        $partJobTitle = array_map('trim', $JobTitle);

        $subject = isset($partJobTitle[1]) && $partJobTitle[1] ? $partJobTitle[1] : 'Job Application Received';
        $this->view('admin::mailApplyJob')
            ->with([
                'email' => $this->application['email'],
                'name' => $this->application['name'],
                'job_id' => $this->application['job_id'],
                'job_title' => $subject,
                'phone' => $this->application['phone'],
                'description' => $this->application['description'],
            ])
            ->subject($subject)
            ->from($this->application['email'], $subject);

        // Attach the files
        // if ($this->uploadedFiles) {
        //     foreach ($this->uploadedFiles as $file) {
        //         if ($file && $file->isValid()) {
        //             // Attach valid file
        //             $email->attach($file->getRealPath(), [
        //                 'as' => $file->getClientOriginalName(),
        //                 'mime' => $file->getMimeType(),
        //             ]);
        //         } else {
        //             // Log or handle invalid file
        //             logger('Invalid file: ' . $file->getClientOriginalName());
        //         }
        //     }
        // }

        // return $email;
    }

    // public function attachments()
    // {
    //     $filesData = [
    //         '../../../public/file_manager/4k-programming-garfield-art-dl5ddmag86uq3ksn-5c9ba529-fc83-4ee1-a159-8e1344f06253.jpeg'
    //     ];
    //     foreach ($this->files as $item) {
    //         $filesData[] = Attachment::fromPath($item);
    //     }
    //     return $filesData;
    // }
    // public function attachments()
    // {
    //     // Initialize with a specific file in public directory
    //     $filesData = [
    //         Attachment::fromPath(public_path('file_manager/4k-programming-garfield-art-dl5ddmag86uq3ksn-5c9ba529-fc83-4ee1-a159-8e1344f06253.jpeg')),
    //         Attachment::fromPath(public_path('file_manager/9-2-a3e37ee8-0b58-470d-af08-097293600204.webp'))
    //     ];

    //     // Add additional attachments from dynamic files
    //     if (!empty($filesData)) {
    //         foreach ($filesData as $item) {
    //             // Assuming files are stored in the public directory, you can adjust the path as needed
    //             $filesData[] = $item;
    //         }
    //     }

    //     return $filesData;
    // }
}
