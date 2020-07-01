@extends('layouts.default')
@section('title', '天鳳通信')

@section('content')


    <div id="capture" style="width: 670px; padding: 10px;">
        <table>
            <caption>早稲田麻雀部 天鳳通信</caption>
            <tr class="head">
                <th id="status">#</th>
                <th id="tenhou_name">天鳳ID</th>
                <th id="grade">段位</th>
                <th id="point">ポイント</th>
                <th id="frequency">打数</th>
            </tr>
            @forelse ($members as $member)
            <tr class="player">
                <td>
                    @if ($member->upgrade === 1)
                    <span class="badge badge-success">昇段</span>
                    @elseif ($member->downgrade === 1)
                    <span class="badge badge-danger">降段</span>
                    @endif
                </td>                
                <td class="big">{{ $member->tenhou_name }}</td>
                @if ($member->last_month_grade !== $member->latest_grade)
                <td class="big">
                    {{ $member->last_month_grade }} → {{ $member->latest_grade }}
                    @if ($member->upgrade === 1)
                    <i class="grade-icon bold success material-icons">trending_up</i>
                    @elseif($member->downgrade === 1)
                    <i class="grade-icon bold danger material-icons">trending_down</i>
                    @endif
                </td>
                <td class="vertical-bottom">{{ $member->latest_point }} pt</td>
                @else
                <td class="big">{{ $member->latest_grade }}</td>
                <td class="vertical-bottom">
                    {{ $member->last_month_point }} → {{ $member->latest_point }} pt
                @if ($member->last_month_point < $member->latest_point)
                    <i class="success material-icons">trending_up</i>
                @elseif($member->last_month_point > $member->latest_point)
                    <i class="danger material-icons">trending_down</i>
                @endif
                </td>
                @endif
                <td class="vertical-bottom">{{ $member->frequency }}</td>
            </tr>
            @empty
            <p>データがありません</p>
            @endforelse
        
    </div>

    <script src = "js/html2canvas.js"></script>
    <script type="text/javascript">
      html2canvas(document.querySelector("#capture")).then(canvas => {
          document.body.appendChild(canvas)
      });
    </script>
@endsection