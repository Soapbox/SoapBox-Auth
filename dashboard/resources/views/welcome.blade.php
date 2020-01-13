@extends('layout')
@section('title', 'Dashboard')

@section('links')
<!-- Fonts -->
<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
@endsection

@section('styles')
<!-- Styles -->
<style>
    html,
    body {
        background-color: #fff;
        color: #636b6f;
        font-family: 'Nunito', sans-serif;
        font-weight: 200;
        height: 100vh;
        margin: 0;
    }

    .full-height {
        height: 100vh;
    }

    .flex-center {
        align-items: center;
        display: flex;
        justify-content: center;
    }

    .position-ref {
        position: relative;
    }

    .top-right {
        position: absolute;
        right: 10px;
        top: 18px;
    }

    .content {
        text-align: center;
    }

    .title {
        font-size: 84px;
    }

    .tiny {
        font-size: 15px;
    }

    .links>a {
        color: #636b6f;
        padding: 0 25px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
    }

    .m-b-md {
        margin-bottom: 30px;
    }
</style>
@endsection

@section('content')
<div class="flex-center position-ref full-height">
    <div class="content">
        <div class="title">
            Soapbox Dashboard
            <p class="tiny">
                <a href="logout">Logout</a>
            </p>

        </div>

        <div>
            Timeframe:
            <a href="/app?days=1">1</a> |
            <a href="/app?days=30">30</a> |
            <a href="/app?days=60">60</a> |
            <a href="/app?days=90">90</a> |
            <a href="/app?days={{ $thisYear }}">This Year</a> |
            <a href="/app?days={{ $allTime }}">All Time</a>
        </div>
        <br />
        <p>Total Results: {{ count($soapboxes) }} </p>
        <hr />
        <div>
            @foreach ($soapboxes as $soapbox)
            <p>
                {{ $soapbox->name }} ( {{ $soapbox->domain }} ) &middot;
                <a href="{{action('DashboardController@showForSlug', ['slug' => $soapbox->slug]) }}">{{ $soapbox->slug }}</a> &middot;
                Count of Users: {{ $soapbox->user_count }}
                Last Active:
                @if ($soapbox->last_active_at)
                {{ \Carbon\Carbon::createFromTimeStamp(strtotime($soapbox->last_active_at))->diffForHumans() }}
                @else
                never
                @endif
            </p>
            @endforeach
        </div>
    </div>
</div>
@endsection