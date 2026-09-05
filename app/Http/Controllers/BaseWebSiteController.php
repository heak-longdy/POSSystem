<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class BaseWebSiteController extends Controller
{
    public $contact;
    public function __construct()
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('contacts')) {
            $this->contact = Contact::first();
            View()->share([
                'contact' => $this->contact,
            ]);
        }
    }
}
