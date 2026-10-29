<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class Tools
{
    public function onSave($table, $req, $id = "", $routerName, $redirect = null, array $messages = [])
    {
        $status = $id ? "Update success." : "Create success.";
        DB::beginTransaction();
        try {
            $item = $req->all();
            $item["user"] = Auth::user()->id;
            if ($id) {
                $table::updateOrCreate(['id' => $id], $item);
            } else {
                $table::create($item);
            }
            DB::commit();
            Session::flash('success', $messages['success'] ?? $status);
            if ($redirect == 'back') {
                return redirect()->back();
            }
            return redirect()->route('admin-'.$routerName.'-list', 1);
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('warning', $messages['warning'] ?? $status);
            return redirect()->back();
        }
    }
    public function onUpdateStatus($table, $id, $status, array $messages = []){
        DB::beginTransaction();
        try {
            $item = [
                "status" => $status,
                "user"=>Auth::user()->id
            ];
            $status = $status == 2 ? "Disable successful!" : "Enable successful!";
            $table::where("id", $id)->update($item);
            DB::commit();
            Session::flash('success', $messages['success'] ?? $status);
            return response()->json([
                'message'=>'success',
                'status'=>200
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('warning', $messages['warning'] ?? 'Status unsuccess!');
            return response()->json([
                'message'=>'unsuccess',
                'status'=>404,
                'error'=>$messages['warning'] ?? $e->getMessage()
            ], 400);
        }
    }
    public function onRestore($table, $id, array $messages = [])
    {
        DB::beginTransaction();
        try {
            $table::withTrashed()->where('id', $id)->restore();
            DB::commit();
            Session::flash('success', $messages['success'] ?? 'Restore success!');
            return response()->json([
                'message'=>'success',
                'status'=>200
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('warning', $messages['warning'] ?? 'Move to restore unsuccess!');
            return response()->json([
                'message'=>'unsuccess',
                'status'=>404,
                'error'=>$messages['warning'] ?? $e
            ]);
        }
    }
    public function onDelete($table, $id, array $messages = []){
        DB::beginTransaction();
        try {
            $table::where('id', $id)->delete();
            DB::commit();
            Session::flash('success', $messages['success'] ?? __('global.message.delete_success'));
            return response()->json([
                'message'=>'success',
                'status'=>200
            ]);
        } catch (Exception $e) {
            DB::rollback();
            Session::flash('warning', $messages['warning'] ?? __('global.message.delete_failed'));
            return response()->json([
                'message'=>'unsuccess',
                'status'=>404,
                'error'=>$messages['warning'] ?? __('global.message.delete_failed')
            ]);
        }
    }
    public function onDestroy($table, $id, array $messages = []){
        DB::beginTransaction();
        try {
            $table::where('id', $id)->forceDelete();
            DB::commit();
            Session::flash('success', $messages['success'] ?? __('global.message.delete_success'));
            return response()->json([
                'message'=>'success',
                'status'=>200
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('warning', $messages['warning'] ?? __('global.message.delete_failed'));
            return response()->json([
                'message'=>'unsuccess',
                'status'=>404,
                'error'=>$messages['warning'] ?? __('global.message.delete_failed')
            ]);
        }
    }
}
