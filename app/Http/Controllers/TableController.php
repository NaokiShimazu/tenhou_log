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
        //段位を数値化
        $grade_number = [
            '新人' => ['number' => 0, 'start' => 0, 'upgrade' => 20],
            '９級' => ['number' => 1, 'start' => 0, 'upgrade' => 20],
            '８級' => ['number' => 2, 'start' => 0, 'upgrade' => 20],
            '７級' => ['number' => 3, 'start' => 0, 'upgrade' => 20],
            '６級' => ['number' => 4, 'start' => 0, 'upgrade' => 40],
            '５級' => ['number' => 5, 'start' => 0, 'upgrade' => 60],
            '４級' => ['number' => 6, 'start' => 0, 'upgrade' => 80],
            '３級' => ['number' => 7, 'start' => 0, 'upgrade' => 100],
            '２級' => ['number' => 8, 'start' => 0, 'upgrade' => 100],
            '１級' => ['number' => 9, 'start' => 0, 'upgrade' => 100],
            '初段' => ['number' => 10, 'start' => 200, 'upgrade' => 400],
            '二段' => ['number' => 11, 'start' => 400, 'upgrade' => 800],
            '三段' => ['number' => 12, 'start' => 600, 'upgrade' => 1200],
            '四段' => ['number' => 13, 'start' => 800, 'upgrade' => 1600],
            '五段' => ['number' => 14, 'start' => 1000, 'upgrade' => 2000],
            '六段' => ['number' => 15, 'start' => 1200, 'upgrade' => 2400],
            '七段' => ['number' => 16, 'start' => 1400, 'upgrade' => 2800],
            '八段' => ['number' => 17, 'start' => 1600, 'upgrade' => 3200],
            '九段' => ['number' => 18, 'start' => 1800, 'upgrade' => 3600],
            '十段' => ['number' => 19, 'start' => 2000, 'upgrade' => 4000],
            '天鳳位' => ['number' => 20],
        ];
        $data = [];
        $file = new \SplFileObject($request->csvfile);
      
        $file->setFlags(
            \SplFileObject::READ_CSV |           // CSV 列として行を読み込む
            \SplFileObject::READ_AHEAD |       // 先読み/巻き戻しで読み出す。
            \SplFileObject::SKIP_EMPTY |         // 空行は読み飛ばす
            \SplFileObject::DROP_NEW_LINE    // 行末の改行を読み飛ばす
        );
        //読み込んだcsvファイルを1行ずつ配列に入れる
        foreach ($file as $line) {
            $pre_data[] = $line;
        }

        $grade_array_key = array_keys($pre_data[0], '段位')[0];
        $point_array_key = array_keys($pre_data[0], 'pt')[0];
        $fluctuation_array_key = array_keys($pre_data[0], 'ptの変動')[0];
        if (!empty(array_keys($pre_data[0], '個室'))) {
            $private_array_key = array_keys($pre_data[0], '個室')[0];
        } else {
            $private_array_key = 100;
        }
        $rule_array_key = array_keys($pre_data[0], 'ルール')[0];

        foreach ($pre_data as $pre_datum) {
            if (empty($pre_datum[$private_array_key]) && strpos($pre_datum[$rule_array_key], '三') === false) {
                $data[] = [
                    'grade' => $pre_datum[$grade_array_key],
                    'point' => intval($pre_datum[$point_array_key]),
                    'fluctuation' => intval($pre_datum[$fluctuation_array_key]),
                ];
            }
        }
        $frequency = count($data);//打数
        $last_month_data = $data[1];//月初のデータ
        $latest_data = end($data);//月終わりのデータ
        $tenhou = app(Tenhou::class);
        $tenhou->real_name = $request->real_name;
        $tenhou->tenhou_name = $request->tenhou_name;
        $tenhou->twitter_id = $request->twitter_id;
        $tenhou->month = $request->month;
        $tenhou->last_month_grade = $last_month_data['grade'];
        $tenhou->last_month_point = $last_month_data['point'];
        $latest_grade_number = $grade_number[$latest_data['grade']]['number'];
        $latest_point = $latest_data['point'] + $latest_data['fluctuation'];
        if ($latest_point < 0) {
            $latest_grade_number -= 1;
            $latest_point = $grade_number[$latest_data['grade']]['start'];
        } else if($latest_point >= $grade_number[$latest_data['grade']]['upgrade']) {
            $latest_grade_number += 1;
            $latest_point = $grade_number[$latest_data['grade']]['start'];
        }
        $tenhou->latest_grade = array_keys(array_filter($grade_number, function($grade_number) use($latest_grade_number){
            return $grade_number['number'] == $latest_grade_number;
        }))[0];
        if ($latest_grade_number > $grade_number[$last_month_data['grade']]['number']) {
            $tenhou->upgrade = 1;
        } else if ($latest_grade_number < $grade_number[$last_month_data['grade']]['number']) {
            $tenhou->downgrade = 1;
        }
        $tenhou->latest_point = $latest_point;
        $tenhou->frequency = $frequency;
        $tenhou->save();

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
            $grade_number = [
                '新人' => ['number' => 0, 'start' => 0, 'upgrade' => 20],
                '９級' => ['number' => 1, 'start' => 0, 'upgrade' => 20],
                '８級' => ['number' => 2, 'start' => 0, 'upgrade' => 20],
                '７級' => ['number' => 3, 'start' => 0, 'upgrade' => 20],
                '６級' => ['number' => 4, 'start' => 0, 'upgrade' => 40],
                '５級' => ['number' => 5, 'start' => 0, 'upgrade' => 60],
                '４級' => ['number' => 6, 'start' => 0, 'upgrade' => 80],
                '３級' => ['number' => 7, 'start' => 0, 'upgrade' => 100],
                '２級' => ['number' => 8, 'start' => 0, 'upgrade' => 100],
                '１級' => ['number' => 9, 'start' => 0, 'upgrade' => 100],
                '初段' => ['number' => 10, 'start' => 200, 'upgrade' => 400],
                '二段' => ['number' => 11, 'start' => 400, 'upgrade' => 800],
                '三段' => ['number' => 12, 'start' => 600, 'upgrade' => 1200],
                '四段' => ['number' => 13, 'start' => 800, 'upgrade' => 1600],
                '五段' => ['number' => 14, 'start' => 1000, 'upgrade' => 2000],
                '六段' => ['number' => 15, 'start' => 1200, 'upgrade' => 2400],
                '七段' => ['number' => 16, 'start' => 1400, 'upgrade' => 2800],
                '八段' => ['number' => 17, 'start' => 1600, 'upgrade' => 3200],
                '九段' => ['number' => 18, 'start' => 1800, 'upgrade' => 3600],
                '十段' => ['number' => 19, 'start' => 2000, 'upgrade' => 4000],
                '天鳳位' => ['number' => 20],
            ];
            
            return $grade_number[$member->latest_grade]['number'];
        });
        return view('screenshot', compact('members'));
    }
}
