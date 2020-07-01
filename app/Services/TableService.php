<?php
namespace App\Services;

use App\Tenhou;

class TableService
{
    public function readPlayerData($file)
    {
        $file = new \SplFileObject($file);
      
        $file->setFlags(
            \SplFileObject::READ_CSV |           // CSV 列として行を読み込む
            \SplFileObject::READ_AHEAD |       // 先読み/巻き戻しで読み出す。
            \SplFileObject::SKIP_EMPTY |         // 空行は読み飛ばす
            \SplFileObject::DROP_NEW_LINE    // 行末の改行を読み飛ばす
        );
        
        return $file;
    }

    public function getDataTitles($file)
    {
        foreach ($file as $line) {
            $data_titles[] = $line;
        }

        return $data_titles[0];
    }

    public function getArrayKeys($data_titles)
    {
        $grade_key = array_keys($data_titles, '段位')[0];
        $point_key = array_keys($data_titles, 'pt')[0];
        $fluctuation_key = array_keys($data_titles, 'ptの変動')[0];
        if (!empty(array_keys($data_titles, '個室'))) {
            $private_key = array_keys($data_titles, '個室')[0];
        } else {
            $private_key = 100;
        }
        $rule_key = array_keys($data_titles, 'ルール')[0];

        return compact(
            'grade_key', 
            'point_key', 
            'fluctuation_key', 
            'private_key',
            'rule_key'
        );
    }

    public function setPlayerData($file, $data_keys)
    {
        foreach ($file as $line) {
            if (empty($line[$data_keys['private_key']]) && $this->isThreePlayer($line[$data_keys['rule_key']]) === false) {
                $data[] = [
                    'grade' => $line[$data_keys['grade_key']],
                    'point' => intval($line[$data_keys['point_key']]),
                    'fluctuation' => intval($line[$data_keys['fluctuation_key']]),
                ];
            }
        }
        return $data;
    }

    public function isThreePlayer($rule_key)
    {
        return strpos($rule_key, '三');
    }

    public function savePlayerData($data, $request)
    {
        $frequency = count($data);//打数
        $last_month_data = $data[0];//月初のデータ
        $latest_data = end($data);//月終わりのデータ

        $tenhou = app(Tenhou::class);

        $tenhou->real_name = $request->real_name;
        $tenhou->tenhou_name = $request->tenhou_name;
        $tenhou->twitter_id = $request->twitter_id;
        $tenhou->month = $request->month;

        $tenhou->last_month_grade = $last_month_data['grade'];
        $tenhou->last_month_point = $last_month_data['point'];

        $latest_grade_number = $this->checkLastGrade($latest_data);
        $latest_point = $this->checkLastPoint($latest_data);
        $latest_point = $latest_data['point'] + $latest_data['fluctuation'];

        $tenhou->latest_grade = $this->convertIntoGrade($latest_grade_number);
        
        $tenhou->upgrade = $this->checkUpgrade($latest_grade_number, $last_month_data);
        $tenhou->downgrade = $this->checkDowngrade($latest_grade_number, $last_month_data);

        $tenhou->latest_point = $latest_point;
        $tenhou->frequency = $frequency;
        $tenhou->save();

    }

    public function checkLastGrade($latest_data)
    {
        $latest_grade_number = config('const.GRADE_NUMBER')[$latest_data['grade']]['number'];
        $latest_point = $latest_data['point'] + $latest_data['fluctuation'];
        if ($latest_point < 0) {
            $latest_grade_number -= 1;
        } else if($latest_point >= config('const.GRADE_NUMBER')[$latest_data['grade']]['upgrade']) {
            $latest_grade_number += 1;
        }

        return $latest_grade_number;
    }

    public function checkLastPoint($latest_data)
    {
        $latest_point = $latest_data['point'] + $latest_data['fluctuation'];

        if ($latest_point < 0) {
            $latest_point = config('const.GRADE_NUMBER')[$latest_data['grade']]['start'];
        } else if($latest_point >= config('const.GRADE_NUMBER')[$latest_data['grade']]['upgrade']) {
            $latest_point = config('const.GRADE_NUMBER')[$latest_data['grade']]['start'];
        }

        return $latest_point;
    }

    public function convertIntoGrade($latest_grade_number)
    {
        $grade_array = config('const.GRADE_NUMBER');
        return array_keys(array_filter($grade_array, function($grade_array) use($latest_grade_number){
                    return $grade_array['number'] == $latest_grade_number;
               }))[0];
    }

    public function checkUpgrade($latest_grade_number, $last_month_data)
    {
        if ($latest_grade_number > config('const.GRADE_NUMBER')[$last_month_data['grade']]['number']) {
            return 1;
        } else {
            return 0;
        }
    }

    public function checkDowngrade($latest_grade_number, $last_month_data)
    {
        if ($latest_grade_number < config('const.GRADE_NUMBER')[$last_month_data['grade']]['number']) {
            return 1;
        } else {
            return 0;
        }
    }
}