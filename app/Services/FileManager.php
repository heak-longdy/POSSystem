<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FileManager
{

    private $fileManager;
    private $folder;
    private $fileStorePath;
    private $perPage;
    
    public function __construct()
    {
        $this->fileManager = 'file_managers';
        $this->folder = 'folders';
        $this->fileStorePath = public_path('file_manager');
        $this->perPage = 20;
    }

    public function index()
    {
        return view('admin::file-manager.index');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function first(Request $request)
    {
        $data['folders'] = DB::table($this->folder)
            ->when(request('q'), function ($query) {
                $query->where('name', 'like', '%' . request('q') . '%');
            })
            ->when(!request('only_trash'), function ($query) {
                $query
                    ->when(request('folder_id'), function ($query) {
                        $query->whereParentId(request('folder_id'));
                    })
                    ->when(!request('folder_id') && !request('q'), function ($query) {
                        $query->whereNull('parent_id');
                    })->whereNull('deleted_at');
            })
            ->when(request('only_trash'), function ($query) {
                $query->whereNotNull('deleted_at');
            })
            ->orderByDesc('created_at')
            ->get();

        $data['files'] = DB::table($this->fileManager)
            ->when(request('q'), function ($query) {
                $query->where('name', 'like', '%' . request('q') . '%');
            })
            ->when(!request('only_trash'), function ($query) {
                $query
                    ->when(request('folder_id'), function ($query) {
                        $query->whereFolderId(request('folder_id'));
                    })
                    ->when(!request('folder_id') && !request('q'), function ($query) {
                        $query->whereNull('folder_id');
                    })->whereNull('deleted_at');
            })
            ->when(request('only_trash'), function ($query) {
                $query->whereNotNull('deleted_at');
            })
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        $data['base_path'] = asset('file_manager');
        return response()->json($data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFiles(Request $request)
    {
        $data['files'] = DB::table($this->fileManager)
            ->when(request('q'), function ($query) {
                $query->where('name', 'like', '%' . request('q') . '%');
            })
            ->when(!request('only_trash'), function ($query) {
                $query
                    ->when(request('folder_id'), function ($query) {
                        $query->whereFolderId(request('folder_id'));
                    })
                    ->when(!request('folder_id') && !request('q'), function ($query) {
                        $query->whereNull('folder_id');
                    })->whereNull('deleted_at');
            })
            ->when(request('only_trash'), function ($query) {
                $query->whereNotNull('deleted_at');
            })
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return response()->json($data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function getFolders(Request $request)
    {
        $folders = DB::table($this->folder);
        if ($request->has('parent_id')) {
            $folders = $folders->where('parent_id', $request->parent_id);
        }
        return response()->json($folders->paginate($request->per_page ?? 10));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getFile($id)
    {
        $file = DB::table($this->fileManager)->find($id);
        if (!$file) {
            return response()->json(['message' => 'file_not_found'], 404);
        }
        return response()->file(asset('file_managers/' . $file->path));
    }

    /**
     * Upload file to database
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */

    public function uploadFile(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'file' => 'required',
        ], [
            'file.required' => 'files_required'
        ]);

        if ($validate->fails()) {
            return response()->json(['message' => $validate->errors()], 422);
        }

        $uploadedFile = $this->upload($request->file('file'), $request->folder_name);
        // foreach ($uploadedFile as $file) {
        DB::table($this->fileManager)->insert([
            'user_id' => auth()->id() ?? null,
            'folder_id' => $request->folder_id ?? null,
            'name' => $uploadedFile->name,
            'path' => $uploadedFile->path,
            'size' => $uploadedFile->size,
            'extension' => $uploadedFile->extension,
            'is_image' => $uploadedFile->is_image,
            'is_video' => $uploadedFile->is_video,
            'is_audio' => $uploadedFile->is_audio,
            'is_document' => $uploadedFile->is_document,
            'width' => $uploadedFile->width,
            'height' => $uploadedFile->height,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // }
        return response()->json(['message' => 'file_uploaded']);
    }

    /**
     * Upload file to storage
     * 
     * @param $file 
     * @param $folder_name string
     * @return Collection
     */
    private function upload($file, ?string $folder_name)
    {
        $uploadedFiles = new Collection();
        if ($file == null) return response()->json(['message' => 'files_empty'], 404);
        // foreach ($files as $file) {
        try {
            $fileInfo = $this->fileInfo($file);
            $fullPath = $folder_name ? $this->fileStorePath . "/$folder_name" : $this->fileStorePath;
            $pathWithFolder = ($folder_name ? "/$folder_name" : '') . "/$fileInfo->file_name.$fileInfo->file_extension";
            $file->move($fullPath, "$fileInfo->file_name.$fileInfo->file_extension");
            $uploadedFiles->push((object)[
                'user_id' => auth()->id() ?? null,
                'folder_id' => $folder_id ?? null,
                'name' => $fileInfo->file_name,
                'path' =>  $pathWithFolder,
                'size' => $fileInfo->file_size,
                'extension' => $fileInfo->file_extension,
                'is_image' => $fileInfo->is_image ? 1 : 0,
                'is_video' => $fileInfo->is_video ? 1 : 0,
                'is_audio' => $fileInfo->is_audio ? 1 : 0,
                'is_document' => $fileInfo->is_document ? 1 : 0,
                'width' => $fileInfo->width,
                'height' => $fileInfo->height,
            ]);
            return $uploadedFiles->first();
        } catch (Exception $error) {
            $this->rollback($uploadedFiles->pluck('path'));
            return response()->json(['message' => $error->getMessage()], 500);
        }
        // }
    }

    /**
     * Rollback file from storage
     * 
     * @param $file
     */
    private function rollback($file)
    {
        if ($file == null) return response()->json(['message' => '_rollback_empty'], 404);
        // foreach ($files as $file) {
        try {
            File::delete($file);
        } catch (Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
        // }
    }

    /**
     * @return object
     */
    private function fileInfo($file)
    {
        if (!is_file($file)) return response()->json(['message' => 'not_file'], 404);
        // $fileName = $file->getClientOriginalName();
        // $existFileName = DB::table($this->fileManager)->where('name', $fileName)->count();
        // $fullName = $existFileName == 0 ? $fileName : "$fileName($existFileName)";
        $fileExtension = $file->getClientOriginalExtension();
        $fileSize = $file->getSize();
        list($width, $height) = getimagesize($file);
        return (object)[
            'file_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '-' . Str::uuid(),
            'file_extension' => $fileExtension,
            'file_size' => $fileSize,
            'is_image' => in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']),
            'is_video' => in_array($fileExtension, ['mp4', 'avi', 'mkv', 'mov']),
            'is_audio' => in_array($fileExtension, ['mp3', 'wav', 'ogg']),
            'is_document' => in_array($fileExtension, ['doc', 'docx', 'pdf', 'txt', 'xls', 'xlsx', 'ppt', 'pptx']),
            'width' => $width ?? null,
            'height' => $height ?? null,
        ];
    }

    /**
     * Delete File
     */
    public function deleteFile(Request $request)
    {
        $file = DB::table($this->fileManager)->find($request->file_id);
        if ($file == null) return response()->json(['message' => 'file_not_found'], 404);
        // if ($file->user_id != auth()->id()) return response()->json(['message' => 'file_not_found'], 404);
        try {
            if ($request->to_trash && $request->to_trash == 'true') {
                DB::table($this->fileManager)->whereId($request->file_id)->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table($this->fileManager)->whereId($request->file_id)->delete();
                File::delete($this->fileStorePath . $file->path);
            }
            return response()->json(['message' => 'file_deleted']);
        } catch (Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function createFolder(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => [
                'bail',
                'required',
                Rule::unique('folders')
                    ->where(function ($query) {
                        $query
                            ->when(request('parent_id'), function ($query) {
                                $query->where('parent_id', request('parent_id'));
                            })
                            ->when(!request('parent_id'), function ($query) {
                                $query->whereNull('parent_id');
                            });
                    })
                    ->where('name', request('name'))
            ],
        ], [
            'name.required' => __('file-manager.create-folder.message.name_required'),
            'name.unique' => __('file-manager.create-folder.message.name_unique'),
        ]);

        if ($validate->fails()) {
            return response()->json(['message' => $validate->errors()], 422);
        }

        try {
            DB::table($this->folder)->insert([
                'user_id' => auth()->id() ?? null,
                'parent_id' => $request->parent_id ?? null,
                'name' => $request->name,
                'color_code' => $request->color ?? null,
                'shortcut' => $request->shortcut ?? null,
                'is_hidden' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return response()->json(['message' => 'folder_created']);
        } catch (Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function renameFolder(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => [
                'bail',
                'required',
                Rule::unique('folders')
                    ->ignore(request('folder_id'), 'id')
                    ->where(function ($query) {
                        $query
                            ->when(request('parent_id'), function ($query) {
                                $query->where('parent_id', request('parent_id'));
                            })
                            ->when(!request('parent_id'), function ($query) {
                                $query->whereNull('parent_id');
                            });
                    })
                    ->where('name', request('name'))
            ],
        ], [
            'name.required' => __('file-manager.create-folder.message.name_required'),
            'name.unique' => __('file-manager.create-folder.message.name_unique'),
        ]);

        if ($validate->fails()) {
            return response()->json(['message' => $validate->errors()], 422);
        }

        try {
            DB::table($this->folder)->where('id', request('folder_id'))->update([
                'name' => $request->name,
                'updated_at' => now(),
            ]);
            return response()->json(['message' => 'folder_updated']);
        } catch (Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    /**
     * Delete Folder
     */
    public function deleteFolder(Request $request)
    {
        $file = DB::table($this->folder)->find($request->folder_id);
        if ($file == null) return response()->json(['message' => 'folder_not_found'], 404);
        // if ($file->user_id != auth()->id()) return response()->json(['message' => 'file_not_found'], 404);
        try {
            if ($request->to_trash && $request->to_trash == 'true') {
                DB::table($this->folder)->whereId($request->folder_id)->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table($this->folder)->whereId($request->folder_id)->delete();
            }
            return response()->json(['message' => 'folder_deleted']);
        } catch (Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    /**
     * Delete All
     */
    public function deleteAll(Request $request)
    {
        if (!isset($request->all) && !isset($request->data)) return response()->json(['message' => 'data_not_found'], 404);
        DB::beginTransaction();
        try {
            if (isset($request->all)) {
                $files = DB::table($this->fileManager)->whereNotNull('deleted_at')->get();
                DB::table($this->fileManager)->whereNotNull('deleted_at')->delete();
                DB::table($this->folder)->whereNotNull('deleted_at')->delete();
                foreach ($files as $file) {
                    File::delete($this->fileStorePath . $file->path);
                }
            } else {
                foreach ($request->data as $item) {
                    if (property_exists((object)$item, 'folder_id')) {
                        DB::table($this->fileManager)->whereNotNull('deleted_at')->whereId($item['id'])->delete();
                        File::delete($this->fileStorePath . $item['path']);
                    } else {
                        DB::table($this->folder)->whereNotNull('deleted_at')->whereId($item['id'])->delete();
                    }
                }
            }
            DB::commit();
            return response()->json(['message' => 'all_deleted']);
        } catch (Exception $error) {
            DB::rollBack();
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    /**
     * Restore All
     */
    public function restoreAll(Request $request)
    {
        if (!isset($request->all) && !isset($request->data)) return response()->json(['message' => 'data_not_found'], 404);
        DB::beginTransaction();
        try {
            $restoreData = [
                'deleted_at' => null,
                'updated_at' => now(),
            ];
            if (isset($request->all)) {
                DB::table($this->fileManager)->whereNotNull('deleted_at')->update($restoreData);
                DB::table($this->folder)->whereNotNull('deleted_at')->update($restoreData);
            } else {
                foreach ($request->data as $item) {
                    if (property_exists((object)$item, 'folder_id')) {
                        DB::table($this->fileManager)->whereNotNull('deleted_at')->whereId($item['id'])->update($restoreData);
                    } else {
                        DB::table($this->folder)->whereNotNull('deleted_at')->whereId($item['id'])->update($restoreData);
                    }
                }
            }
            DB::commit();
            return response()->json(['message' => 'all_restored']);
        } catch (Exception $error) {
            DB::rollBack();
            return response()->json(['message' => $error->getLine()], 500);
        }
    }
}
