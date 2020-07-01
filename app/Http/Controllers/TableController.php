<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\InsertRequest;
use App\Tenhou;
use App\Services\TableService;

class TableController extends Controller
{
    public function __construct(TableService $table_service)
    {
        $this->table_service = $table_service;
    }

    public function view_table()
    {
        $members = Tenhou::all();
        return view('tenhou_table', compact('members'));
    }

    public function add(InsertRequest $request)
    {
        $data = [];

        $file = $this->table_service->readPlayerData($request->csvfile);
        $data_titles = $this->table_service->getDataTitles($file);
        $data_keys = $this->table_service->getArrayKeys($data_titles);
        $data = $this->table_service->setPlayerData($file, $data_keys);

        $this->table_service->savePlayerData($data, $request);

        return redirect('/tenhou');
    }

    public function destroy($id)
    {
        Tenhou::find($id)->delete();

        return redirect(route('index'));
    }

    public function screenshot()
    {

        $members = Tenhou::all()->sortByDesc(function($member) {
            return config('const.GRADE_NUMBER')[$member->latest_grade]['number'];
        });

        return view('screenshot', compact('members'));
    }
}
