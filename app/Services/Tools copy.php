<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class Tools
{
    public function onSave($table, $req, $id = "", $routerName, $redirect = null)
    {
        $status = $id ? "Update success." : "Create success.";
        DB::beginTransaction();
        try {
            $item = $req->all();
            if (Auth::check()) {
                $tableName = (new $table)->getTable();
                if (Schema::hasColumn($tableName, 'user')) {
                    $item['user'] = Auth::user()->id;
                } elseif (Schema::hasColumn($tableName, 'user_id')) {
                    $item['user_id'] = Auth::user()->id;
                }
            }
            $table::updateOrCreate(['id' => $id], $item);
            DB::commit();
            Session::flash('success', $status);
            if ($redirect == 'back') {
                return redirect()->back();
            }
            return redirect()->route('admin-'.$routerName.'-list', 1);
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('warning', $status);
            return redirect()->back();
        }
    }
    public function onUpdateStatus($table, $id, $status){
        DB::beginTransaction();
        try {
            $tableName = (new $table)->getTable();
            $item = [
                "status" => (int)$status,
            ];
            if (Auth::check()) {
                if (Schema::hasColumn($tableName, 'user')) {
                    $item['user'] = Auth::user()->id;
                } elseif (Schema::hasColumn($tableName, 'user_id')) {
                    $item['user_id'] = Auth::user()->id;
                }
            }
            $statusMessage = ((int)$status === 2) ? "Disable successful!" : "Enable successful!";
            $table::where("id", $id)->update($item);
            DB::commit();
            Session::flash('success', $statusMessage);
            return response()->json([
                'message'=>'success',
                'status'=>200
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('warning', 'Status unsuccess!');
            return response()->json([
                'message'=>'unsuccess',
                'status'=>404,
                'error'=>$e->getMessage()
            ], 400);
        }
    }
    public function onRestore($table,$id)
    {
        DB::beginTransaction();
        try {
            $table::withTrashed()->where('id', $id)->restore();
            DB::commit();
            Session::flash('success', 'Restore success!');
            return response()->json([
                'message'=>'success',
                'status'=>200
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('warning', 'Move to restore unsuccess!');
            return response()->json([
                'message'=>'unsuccess',
                'status'=>404,
                'error'=>$e
            ]);
        }
    }
    public function onDelete($table,$id){
        DB::beginTransaction();
        try {
            $table::where('id', $id)->delete();
            DB::commit();
            Session::flash('success', 'Delete success!');
            return response()->json([
                'message'=>'success',
                'status'=>200
            ]);
        } catch (Exception $e) {
            DB::rollback();
            Session::flash('warning', 'Delete unsuccess!');
            return response()->json([
                'message'=>'unsuccess',
                'status'=>404,
                'error'=>$e
            ]);
        }
    }
    public function onDestroy($table,$id){
        DB::beginTransaction();
        try {
            $table::where('id', $id)->forceDelete();
            DB::commit();
            Session::flash('success', 'Delete success!');
            return response()->json([
                'message'=>'success',
                'status'=>200
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('warning', 'Delete unsuccess!');
            return response()->json([
                'message'=>'unsuccess',
                'status'=>404,
                'error'=>$e
            ]);
        }
    }
}
