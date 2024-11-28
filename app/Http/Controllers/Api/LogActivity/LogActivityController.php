<?php

namespace App\Http\Controllers\Api\LogActivity;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\LogActivity\LogActivity;
use Illuminate\Http\Request;

class LogActivityController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('role:posko-utama');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Mengambil daftar Log Activity dengan pagination 10 item per halaman
            $log_activity = LogActivity::paginate(10);

            // Mengembalikan response sukses dengan data Log Activity
            return ApiResponse::success($log_activity);
        } catch (\Throwable $th) {
            // Menangkap exception dan mengembalikan pesan error
            return ApiResponse::badRequest($th->getMessage());
        }
    }
}
