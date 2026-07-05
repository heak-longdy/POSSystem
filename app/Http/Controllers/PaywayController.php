<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use Illuminate\Http\Request;
use App\Models\WalletHistory;
use Illuminate\Support\Facades\DB;

class PaywayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $arrColumnNames = ['order_id', 'amount', 'firstname', 'lastname', 'phone'];

    public function index()
    {
        return view('admin::payway.viewData');
    }

    public function length_charecter_60($string, $addon)
    {
        $string = substr($string, 0, 60);
        if (strlen($string) < 10) {
            $string = $string . ' ' . $addon;
        }
        if (strlen($string) > 60) {
            $string = substr($string, 0, 60);
        }
        return $string;
    }

    public function remove_special_sign($string)
    {
        $pattern = '/[^a-z A-Z0-9\-]/i';
        $replacement = '';
        return preg_replace($pattern, $replacement, $string);
    }


    public function getHash($concat_params, $ABA_PAYWAY_API_KEY)
    {
        $hash = base64_encode(hash_hmac('sha512', $concat_params, $ABA_PAYWAY_API_KEY, true));
        return $hash;
    }

    public function payway_form(Request $request)
    {
        $ABA_PAYWAY_API_URL = "https://checkout-sandbox.payway.com.kh/api/payment-gateway/v1/payments/purchase";
        $ABA_PAYWAY_API_KEY = "aa2f5d25-3667-43b0-9d21-3169ebc135a5";
        $ABA_PAYWAY_MERCHANT_ID = "simplebarber";
        $input = $request->all();
        $check_excel_existing_columns = true;
        foreach ($this->arrColumnNames as $eachColumn) {
            if (array_key_exists($eachColumn, $input)) {
            } else {
                $check_excel_existing_columns = false;
            }
        }
        if ($check_excel_existing_columns) {
            $req_time = time();
            $tran_id = $input['order_id'];
            $amount = $input['amount'];
            $firstname = $input['firstname'];
            $lastname = $input['lastname'];
            $phone = $input['phone'];
            $payment_option = "bakong";
            $view_type = "hosted_view";
            $return_url = base64_encode('https://barbershop.phsartech.com/payway-submit');
            $continue_success_url = 'https://barbershop.phsartech.com/status=success';
            $cancel_url = 'https://barbershop.phsartech.com/status=cancel';
            $return_params = "json";

            $concat_params = $req_time . $ABA_PAYWAY_MERCHANT_ID . $tran_id . $amount . $firstname  . $lastname . $phone . $payment_option . $return_url . $cancel_url . $continue_success_url . $return_params;
            $hash = $this->getHash($concat_params, $ABA_PAYWAY_API_KEY);
            $params = array(
                "hash" => $hash,
                "req_time" => $req_time,
                "merchant_id" => $ABA_PAYWAY_MERCHANT_ID,
                "tran_id" => $tran_id,
                "amount" => $amount,
                "firstname" => $firstname,
                "lastname" => $lastname,
                "phone" => $phone,
                "payment_option" => $payment_option,
                "view_type" => $view_type,
                "return_url" => $return_url,
                "continue_success_url" => $continue_success_url,
                "cancel_url" => $cancel_url,
                "return_params" => $return_params

            );
            return view('admin::payway.index', compact(['params', 'ABA_PAYWAY_API_URL']));
        } else {
            return view('page_not_found');
        }
    }

    public function payment_form_confirmation(Request $request)
    {
        return view('cybersource.payment_confirmation');
    }

    public function paymentSubmit(Request $req)
    {
        DB::beginTransaction();
        try {
            $dataCallback = $req->data ? (object) json_decode($req->data) : null;
            $walletHistory = WalletHistory::where('tran_id', $dataCallback->tran_id)->first();
            if ($dataCallback->status == 0 && $walletHistory->status != 2) {
                $barber = Barber::find($walletHistory->barber_id);
                $wallet = $barber->wallet + $walletHistory->amount;
                $barber->update(['wallet' => $wallet]);
                $walletHistory->update(['status' => 2]);
                DB::commit();
                return "payment_success";
            } else if ($walletHistory->status == 2) {
                return "payment_ready";
            }
            return "payment_fail";
        } catch (\Exception $e) {
            DB::rollback();
            return "payment_fail";
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
