@extends('layouts.default')
@section('title', '天鳳通信')

@section('content')

<h1>天鳳通信管理ページ</h1>
@foreach ($errors->all() as $error)
<p>{{ $error }}</p>
@endforeach
<form action="/add" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    <label class="text-facebook">名前: <input type="text" name="real_name"></label>
    <label>天鳳名: <input type="text" name="tenhou_name"></label>
    <label>Twitter: <input type="text" name="twitter_id"></label>
    <label>年月: <input type="month" name="month"></label>
    <input type="file" name="csvfile">
    <input type="submit" value="送信">
</form>

<table>
    <tr class="head">
        <th>#</th>
        <th>名前</th>
        <th>段位</th>
        <th>ポイント</th>
        <th>天鳳ID</th>
        <th>打数</th>
        <th>Twitter</th>
        <th>操作</th>
    </tr>
    @forelse ($members as $member)
    <tr class="player">
        <td>
            @if ($member->upgrade === 1)
            <span class="badge badge-success">昇段</span>
            @elseif ($member->downgrade === 1)
            <span class="badge badge-danger">降段</span>
            @elseif ($member->frequency < 10) <span class="badge badge-primary">保存</span>
                @endif
        </td>
        <td class="big">{{ $member->real_name }}</td>
        @if ($member->last_month_grade !== $member->latest_grade)
        <td class="big">
            {{ $member->last_month_grade }} -> {{ $member->latest_grade }}
            @if ($member->upgrade === 1)
            <span class="bold success"> ↑</span>
            @elseif($member->downgrade === 1)
            <span class="bold danger"> ↓</span>
            @endif
        </td>
        <td>{{ $member->latest_point }} pt</td>
        @else
        <td class="big">{{ $member->latest_grade }}</td>
        <td>
            {{ $member->last_month_point }} -> {{ $member->latest_point }} pt
        @if ($member->last_month_point < $member->latest_point)
            <span class="bold success"> ↑</span>
        @elseif($member->last_month_point > $member->latest_point)
            <span class="bold danger"> ↑</span>
        @endif
        </td>
        @endif
        <td>{{ $member->tenhou_name }}</td>
        <td>{{ $member->frequency }}</td>
        <td>{{ $member->twitter_id }}</td>
        <td>
            <form action="{{ route('delete', ['id' => $member->id]) }}" method="post">
                {{ csrf_field() }}
                {{ method_field('delete') }}
                <input class="btn btn-danger" type="submit" value="削除">
            </form>
        </td>
    </tr>
    @empty
    <p>データがありません</p>
    @endforelse
</table>
<a href="{{ route('screenshot') }}">画像化</a>
@endsection