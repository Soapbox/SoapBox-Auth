@extends('layout')
@section('title')
Dashboard - {{ $slug }}
@endsection

@section('links')
<!-- Fonts -->
<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
@endsection

@section('styles')
<!-- Styles -->
<style>
    body {
        background: #f5f6f8;
    }

    .tiny-subheading span {
        position: relative;
        z-index: 2;
        background-color: #f5f6f8;
        padding-right: 7px;
    }

    .tiny-subheading hr {
        top: 7px;
    }

    .tab-toggle__big-chart {
        cursor: pointer;
        border: 2px solid white;
        transition: 0.3s box-shadow;
    }

    .tab-toggle__big-chart:hover {
        border-color: #bddefd;
    }

    .tab-toggle__big-chart.tab-active {
        border-color: #0e82ee;
    }

    .mini-chart {
        position: relative;
    }

    .mini-chart__graph {
        height: 100px;
    }

    .mini-chart__benchmark {
        position: absolute;
        top: 0;
        right: 0;
    }

    .mini-chart__delta {
        position: absolute;
        bottom: 0;
        right: 0;
    }

    .big-chart {
        position: relative;
    }

    .big-chart__value {
        position: absolute;
        top: 0;
        right: 0;
    }

    .big-chart__legend {
        position: absolute;
        top: -35px;
        right: 0;
    }

    .big-chart__graph {
        height: 400px;
    }
</style>
@endsection

@section('content')
<!-- Header-->
<h1>
    Dashboard &middot; {{ $slug }}
</h1>

<!-- Sub Header-->
<h4 class="tiny-subheading text-secondary text-uppercase h6 small pt-3 pb-2 position-relative">
    <span>General information about <strong>{{ $soapbox ? $soapbox->name : "Unknown" }}</strong> ( {{ $soapbox ? $soapbox->domain : "Unknown" }} )</span>
    <hr class="position-absolute align-middle w-100">
</h4>

<div>
    <p>
        Active Users: <strong>{{ $soapbox ? $soapbox->user_count : "0" }}</strong> &middot;
        Deferred Users: <strong>{{ $soapbox ? $soapbox->deferred_count : "0" }}</strong> &middot;
        Deactivated Users: <strong>{{ $soapbox ? $soapbox->deactivated_count: "0" }}</strong>
    </p>
</div>

<!-- Sub Header-->
<h4 class="tiny-subheading text-secondary text-uppercase h6 small pt-3 pb-2 position-relative">
    <span>How is your team performing against the benchmarks?</span>
    <hr class="position-absolute align-middle w-100">
</h4>

<!-- Mini Charts or like... tabs? -->
<div class="row">
    <div class="col">
        <!-- Tab Toggle #1 -->
        <div data-tab="big-chart-closer" class="tab-active tab-toggle__big-chart shadow-lg p-3 mb-4 bg-white rounded">
            <div class="mini-chart">
                <div class="mini-chart__heading text-center pb-2 pt-4 h6">
                    <div class="h3">{{ $closeRatioScore }}</div>
                    Avg. Close Ratio
                </div>
                <div class="mini-chart__graph mb-3">
                    {!! $closeRatioChart->container() !!}
                </div>
                <div class="mini-chart__benchmark text-right text-muted small">
                    Benchmark
                    <div>79%</div>
                </div>
                <div class="mini-chart__delta small @if($closeRatioPercentageChange < -10) text-danger @elseif($closeRatioPercentageChange < 1) text-warning @else text-success @endif">
                    @if($closeRatioPercentageChange < 0) &darr; @elseif($closeRatioPercentageChange> 0) &uarr; @endif {{ sprintf("%.0f%%", $closeRatioPercentageChange) }}
                </div>
            </div>
            <h6>How well does your team close items on their meeting agendas?</h6>
            <p class="text-secondary small">Good &mdash; 76% of items on agedas get discussed by your teams.</p>
        </div>
    </div>
    <div class="col">
        <!-- Tab Toggle #2 -->
        <div data-tab="big-chart-rater" class="tab-toggle__big-chart shadow-lg p-3 mb-4 bg-white rounded">
            <div class="mini-chart">
                <div class="mini-chart__heading text-center pb-2 pt-4 h6">
                    <div class="h3">{{ $meetingRatingScore }}</div>
                    Avg. Meeting Rating
                </div>
                <div class="mini-chart__graph mb-3">
                    {!! $meetingRatingChart->container() !!}
                </div>
                <div class="mini-chart__benchmark text-right text-muted small">
                    Benchmark
                    <div>75%</div>
                </div>
                <div class="mini-chart__delta small @if($meetingRatingPercentageChange < -10) text-danger @elseif($meetingRatingPercentageChange < 1) text-warning @else text-success @endif">
                    @if($meetingRatingPercentageChange < 0) &darr; @elseif($meetingRatingPercentageChange> 0) &uarr; @endif {{ sprintf("%.0f%%", $meetingRatingPercentageChange) }}
                </div>
            </div>
            <h6>How well does your team close items on their meeting agendas?</h6>
            <p class="text-secondary small">Good &mdash; 76% of items on agedas get discussed by your teams.</p>
        </div>
    </div>

    {{--
                <div class="col">
                    <!-- Tab Toggle #3 -->
                    <div data-tab="big-chart-balance" class="tab-toggle__big-chart shadow-lg p-3 mb-4 bg-white rounded">
                        <div class="mini-chart">
                            <div class="mini-chart__heading text-center pb-2 pt-4 h6">
                                <div class="h3">45%</div>
                                Discussion Balance
                            </div>
                            <div class="mini-chart__graph mb-3">
                                <!-- Replace this shit with your cool chart -->
                                <div style="width:100%;height:100px;background:#cbe2ff;"></div>
                                <!-- / Replace this shit with your cool chart -->
                            </div>
                            <div class="mini-chart__benchmark text-right text-muted small">
                                Benchmark
                                <div>66%</div>
                            </div>
                            <div class="mini-chart__delta small text-danger">
                                &darr; 21%
                            </div>
                        </div>
                        <h6>How well does your team close items on their meeting agendas?</h6>
                        <p class="text-secondary small">Good &mdash; 76% of items on agedas get discussed by your teams.</p>
                    </div>
                </div>
--}}
</div>

<div class="row">
    <div class="col">
        <!-- Tab #1 -->
        <div id="big-chart-closer" class="d-block tab-content__big-chart shadow-lg p-3 pt-4 mb-3 bg-white rounded">
            <h5>Avg. Close Ratio &mdash; Your team vs. benchmarks</h5>
            <p class="text-secondary small">Your team is up...</p>
            <div class="big-chart">
                <div class="big-chart__graph mb-3">
                    {!! $closeRatioBenchmarkChart->container() !!}
                </div>
                <div class="big-chart__value h2 text-right">
                    <span class="@if($closeRatioPercentageChange < -10) text-danger @elseif($closeRatioPercentageChange < 1) text-warning @else text-success @endif h6">@if($closeRatioPercentageChange < 0) &darr; @elseif($closeRatioPercentageChange> 0) &uarr; @endif {{ sprintf("%.0f%%", $closeRatioPercentageChange) }}</span>
                    {{ $closeRatioScore }}
                </div>
                <div class="big-chart__legend small text-muted">
                    <span style="display: inline-block;width:10px;height:10px;border-radius: 50%;margin:0 2px 0 10px;background: #41a3fe;"></span> Company
                    <span style="display: inline-block;width:10px;height:10px;border-radius: 50%;margin:0 2px 0 10px;background: #fdd176;"></span> Industry
                    <span style="display: inline-block;width:10px;height:10px;border-radius: 50%;margin:0 2px 0 10px;background: #c3c0d0;"></span> Benchmark
                </div>
            </div>
        </div>
        <!-- Tab #2 -->
        <div id="big-chart-rater" class="d-none tab-content__big-chart shadow-lg p-3 pt-4 mb-3 bg-white rounded">
            <h5>Avg. Meeting Rating &mdash; Your team vs. benchmarks</h5>
            <p class="text-secondary small">Your team is up...</p>
            <div class="big-chart">
                <div class="big-chart__graph mb-3">
                    {!! $meetingRatingBenchmarkChart->container() !!}
                </div>
                <div class="big-chart__value h2 text-right">
                    <span class="@if($meetingRatingPercentageChange < -10) text-danger @elseif($meetingRatingPercentageChange < 1) text-warning @else text-success @endif h6">@if($meetingRatingPercentageChange < 0) &darr; @elseif($meetingRatingPercentageChange> 0) &uarr; @endif {{ sprintf("%.0f%%", $meetingRatingPercentageChange) }}</span>
                    {{ $meetingRatingScore }}
                </div>
                <div class="big-chart__legend small text-muted">
                    <span style="display: inline-block;width:10px;height:10px;border-radius: 50%;margin:0 2px 0 10px;background: #41a3fe;"></span> Company
                    <span style="display: inline-block;width:10px;height:10px;border-radius: 50%;margin:0 2px 0 10px;background: #fdd176;"></span> Industry
                    <span style="display: inline-block;width:10px;height:10px;border-radius: 50%;margin:0 2px 0 10px;background: #c3c0d0;"></span> Benchmark
                </div>
            </div>
        </div>

        <!-- Tab #3 -->
        <div id="big-chart-balance" class="d-none tab-content__big-chart shadow-lg p-3 pt-4 mb-3 bg-white rounded">
            <h5>Discussion Balance &mdash; Your team vs. benchmarks</h5>
            <p class="text-secondary small">Your team is up...</p>
            <div class="big-chart">
                <div class="big-chart__graph mb-3">
                    <!-- Replace this shit with your cool chart -->
                    <div style="width:100%;height:300px;background:#cbe2ff;"></div>
                    <!-- / Replace this shit with your cool chart -->
                </div>
                <div class="big-chart__value h2 text-right">
                    <span class="text-success h6">&uarr; 66%</span>
                    76%
                </div>
                <div class="big-chart__legend small text-muted">
                    <span style="display: inline-block;width:10px;height:10px;border-radius: 50%;margin:0 2px 0 10px;background: #41a3fe;"></span> Your Team
                    <span style="display: inline-block;width:10px;height:10px;border-radius: 50%;margin:0 2px 0 10px;background: #fdd176;"></span> Your Company
                    <span style="display: inline-block;width:10px;height:10px;border-radius: 50%;margin:0 2px 0 10px;background: #c3c0d0;"></span> Industry
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Sub Header-->
<h4 class="tiny-subheading text-secondary text-uppercase h6 small mt-5 pt-3 pb-2 position-relative">
    <span>Who are the outliers on your team?</span>
    <hr class="position-absolute align-middle w-100">
</h4>

<div class="row">
    <div class="col">
        <div class="shadow-lg h-100 p-3 pt-4 mb-3 bg-white rounded">
            <div class="text-center">
                <span style="border-radius:50%;background:#edf1f0;width:30px;height:30px;line-height:30px;display:inline-block;font-size:18px;text-align: center;padding: 2px 0 0 4px;">
                    😬
                </span>
            </div>
            <h6 class="text-center mt-2">Which managers aren't running one-on-ones with their team?</h6>

            <table class="table table-borderless table-sm" style="font-size:75%">
                <thead>
                    <tr>
                        <th scope="col" class="text-muted">Teammate</th>
                        <th scope="col" class="text-muted text-right">Last 1:1</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users_not_having_meetings as $user)
                    <tr>
                        <td class="pb-2">
                            <img class="rounded-circle float-left mr-2" style="width:25px;height:25px;" src="{{ $user->avatar }}">
                            <span class="h6" style="padding-top:3px;">{{ $user->name }}</span>
                        </td>
                        <td class="text-right pb-2">
                            <span class="h6" style="padding-top:3px;">{{ \Carbon\Carbon::createFromTimeStamp(strtotime($user->max_created_at))->diffForHumans() }}</span>
                        </td>
                    </tr>
                    @endforeach
                    @if(count($users_not_having_meetings) == 0)
                    <tr>
                        <td span="2">
                            <span class="text-muted">None to display</span>
                    </tr>
                    @endif
                    <!--
                                        <tr>
                                            <td class="pb-2">
                                                <img class="rounded-circle float-left mr-2" style="width:25px;height:25px;" src="https://pbs.twimg.com/profile_images/613552234991779840/5SAehr3r_400x400.png">
                                                <span class="h6" style="padding-top:3px;">Bruce Bobby</span>
                                            </td>
                                            <td class="text-right pb-2">
                                                <span class="h6" style="padding-top:3px;">7 weeks ago</span>
                                            </td>
                                        </tr>
                                        -->
                </tbody>
            </table>
        </div>
    </div>
    <div class="col">
        <div class="shadow-lg h-100 p-3 pt-4 mb-3 bg-white rounded">
            <div class="text-center">
                <span style="border-radius:50%;background:#edf1f0;width:30px;height:30px;line-height:30px;display:inline-block;font-size:18px;text-align: center;padding: 2px 0 0 4px;">
                    🏆
                </span>
            </div>
            <h6 class="text-center mt-2">Who is closing the most items on your team?</h6>

            <table class="table table-borderless table-sm" style="font-size:75%">
                <thead>
                    <tr>
                        <th scope="col" class="text-muted">Teammate</th>
                        <th scope="col" class="text-muted text-right">Avg. Close Ratio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users_best_close_ratio as $user)
                    <tr>
                        <td class="pb-2">
                            <img class="rounded-circle float-left mr-2" style="width:25px;height:25px;" src="{{ $user->avatar }}">
                            <span class="h6" style="padding-top:3px;">{{ $user->name }}</span>
                        </td>
                        <td class="text-right pb-2">
                            <span class="h6" style="padding-top:3px;">
                                @if ($user->average_close_ratio)
                                {{ sprintf('%.0f%%', $user->average_close_ratio*100) }}
                                @else
                                <span class="text-muted small">No data</span>
                                @endif
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    @if(count($users_best_close_ratio) == 0)
                    <tr>
                        <td span="2">
                            <span class="text-muted">None to display</span>
                    </tr>
                    @endif
                    <!--
                                    <tr>
                                        <td class="pb-2">
                                            <img class="rounded-circle float-left mr-2" style="width:25px;height:25px;" src="https://pbs.twimg.com/profile_images/613552234991779840/5SAehr3r_400x400.png">
                                            <span class="h6" style="padding-top:3px;">Bruce Bobby</span>
                                        </td>
                                        <td class="text-right pb-2">
                                            <span class="h6" style="padding-top:3px;">100%</span>
                                        </td>
                                    </tr>
                                    -->
                </tbody>
            </table>
        </div>
    </div>
    <div class="col">
        <div class="shadow-lg h-100 p-3 pt-4 mb-3 bg-white rounded">
            <div class="text-center">
                <span style="border-radius:50%;background:#edf1f0;width:30px;height:30px;line-height:30px;display:inline-block;font-size:18px;text-align: center;padding: 2px 0 0 4px;">
                    🤔
                </span>
            </div>
            <h6 class="text-center mt-2">Which managers on your team are in need of help?</h6>

            <table class="table table-borderless table-sm" style="font-size:75%">
                <thead>
                    <tr>
                        <th scope="col" class="text-muted">Teammate</th>
                        <th scope="col" class="text-muted text-right">Avg. Team Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users_worst_average_meeting_rating as $user)
                    <tr>
                        <td class="pb-2">
                            <img class="rounded-circle float-left mr-2" style="width:25px;height:25px;" src="{{ $user->avatar }}">
                            <span class="h6" style="padding-top:3px;">{{ $user->name }}</span>
                        </td>
                        <td class="pb-2">
                            @if ($user->average_meeting_rating)
                            <div class="progress mt-2 float-right" style="height: 12px; width:100%;">
                                <div class="progress-bar @if($user->average_meeting_rating < 0.65) bg-danger @elseif($user->average_meeting_rating < 0.80) bg-warning @else bg-success @endif" role="progressbar" style="width: {{ sprintf('%.0f%%', $user->average_meeting_rating*100) }}" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            @else
                            <span class="text-muted small"> No data</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    @if(count($users_worst_average_meeting_rating) == 0)
                    <tr>
                        <td span="2">
                            <span class="text-muted">None to display</span>
                    </tr>
                    @endif
                    <!--
                                <tr>
                                    <td class="pb-2">
                                        <img class="rounded-circle float-left mr-2" style="width:25px;height:25px;" src="https://pbs.twimg.com/profile_images/613552234991779840/5SAehr3r_400x400.png">
                                        <span class="h6" style="padding-top:3px;">Bruce Bobby</span>
                                    </td>
                                    <td class="pb-2">
                                        <div class="progress mt-2 float-right" style="height: 12px; width:100%;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 66%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="pb-2">
                                        <img class="rounded-circle float-left mr-2" style="width:25px;height:25px;" src="https://pbs.twimg.com/profile_images/613552234991779840/5SAehr3r_400x400.png">
                                        <span class="h6" style="padding-top:3px;">Sally Salamander</span>
                                    </td>
                                    <td class="pb-2">
                                        <div class="progress mt-2 float-right" style="height: 12px; width:100%;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 70%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="pb-2">
                                        <img class="rounded-circle float-left mr-2" style="width:25px;height:25px;" src="https://pbs.twimg.com/profile_images/613552234991779840/5SAehr3r_400x400.png">
                                        <span class="h6" style="padding-top:3px;">Super Duper Dude</span>
                                    </td>
                                    <td class="pb-2">
                                        <div class="progress mt-2 float-right" style="height: 12px; width:100%;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                </tr>
                                -->
                </tbody>
            </table>

        </div>
    </div>
</div>

<!-- Sub Header-->
<h4 class="tiny-subheading text-secondary text-uppercase h6 small mt-5 pt-3 pb-2 position-relative">
    <span>What is the breakdown of all of my managers?</span>
    <hr class="position-absolute align-middle w-100">
</h4>

<div class="shadow-lg p-4 mb-3 bg-white rounded">
    <h2 class="mb-4">Your Team</h2>
    <table class="table" style="">
        <thead>
            <tr>
                <th scope="col" class="text-muted">Name</th>
                <th scope="col" class="text-muted"># of 1:1s</th>
                <th scope="col" class="text-muted"># of Meetings</th>
                <th scope="col" class="text-muted">Avg. Agenda Items</th>
                <th scope="col" class="text-muted">Avg. Close Ratio</th>
                <!--<th scope="col" class="text-muted">Discussion</th>-->
                <th scope="col" class="text-muted">Avg. Rating</th>
            </tr>
        </thead>
        <tbody>
            <div>
                @foreach($users as $user)
                <tr>
                    <td class="pb-2">
                        <img class="rounded-circle float-left mr-2" style="width:25px;height:25px;" src="{{ $user->avatar }}">
                        <span class="h6" style="padding-top:3px;">{{ $user->name }}</span>
                    </td>
                    <td class="pb-2">{{ $user->num_of_channels }}</td>
                    <td class="pb-2">{{ $user->num_of_meetings }}</td>
                    <td class="pb-2">
                        @if ($user->average_agenda_items)
                        {{ round($user->average_agenda_items,1) }}
                        @else
                        <span class="text-muted small"> No data</span>
                        @endif
                    </td>
                    <td class="pb-2">
                        @if ($user->average_close_ratio)
                        {{ sprintf('%.0f%%', $user->average_close_ratio*100) }}
                        @else
                        <span class="text-muted small"> No data</span>
                        @endif
                    </td>
                    <!--<td class="pb-2">️⚖️ Balanced</td>-->
                    <td class="pb-2">
                        @if ($user->average_meeting_rating)
                        <div class="progress mt-2 float-right" style="height: 12px; width:100%;">
                            <div class="progress-bar @if($user->average_meeting_rating < 0.65) bg-danger @elseif($user->average_meeting_rating < 0.80) bg-warning @else bg-success @endif" role="progressbar" style="width: {{ sprintf('%.0f%%', $user->average_meeting_rating*100) }}" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        @else
                        <span class="text-muted small"> No data</span>
                        @endif
                    </td>
                </tr>
                @endforeach

                <!--
                            <tr>
                                <td class="pb-2">
                                    <img class="rounded-circle float-left mr-2" style="width:25px;height:25px;" src="https://pbs.twimg.com/profile_images/613552234991779840/5SAehr3r_400x400.png">
                                    <span class="h6" style="padding-top:3px;">Bobby Bruce</span>
                                </td>
                                <td class="pb-2">7</td>
                                <td class="pb-2">12</td>
                                <td class="pb-2">76%</td>
                                <td class="pb-2">️⚖️ Balanced</td>
                                <td class="pb-2">
                                    <div class="progress mt-2 float-right" style="height: 12px; width:100%;">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 25%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="pb-2">
                                    <img class="rounded-circle float-left mr-2" style="width:25px;height:25px;" src="https://pbs.twimg.com/profile_images/613552234991779840/5SAehr3r_400x400.png">
                                    <span class="h6" style="padding-top:3px;">Bruce Bobby</span>
                                </td>
                                <td class="pb-2">12</td>
                                <td class="pb-2">8</td>
                                <td class="pb-2">69%</td>
                                <td class="pb-2">🤯 Disordered</td>
                                <td class="pb-2">
                                    <div class="progress mt-2 float-right" style="height: 12px; width:100%;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 66%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="pb-2">
                                    <img class="rounded-circle float-left mr-2" style="width:25px;height:25px;" src="https://pbs.twimg.com/profile_images/613552234991779840/5SAehr3r_400x400.png">
                                    <span class="h6" style="padding-top:3px;">Sally Salamander</span>
                                </td>
                                <td class="pb-2">3</td>
                                <td class="pb-2">2</td>
                                <td class="pb-2">100%</td>
                                <td class="pb-2">🎢 Unbalanced</td>
                                <td class="pb-2">
                                    <div class="progress mt-2 float-right" style="height: 12px; width:100%;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 70%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="pb-2">
                                    <img class="rounded-circle float-left mr-2" style="width:25px;height:25px;" src="https://pbs.twimg.com/profile_images/613552234991779840/5SAehr3r_400x400.png">
                                    <span class="h6" style="padding-top:3px;">Super Duper Dude</span>
                                </td>
                                <td class="pb-2">30</td>
                                <td class="pb-2">7</td>
                                <td class="pb-2">51%</td>
                                <td class="pb-2">⚖️ Balanced</td>
                                <td class="pb-2">
                                    <div class="progress mt-2 float-right" style="height: 12px; width:100%;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 75%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </td>
                            </tr>
                            -->
        </tbody>
    </table>
</div>

@endsection

@section('scripts')
@parent
<script>
    $(function() {
        $('.tab-toggle__big-chart').on('click', function() {
            $('.tab-toggle__big-chart').removeClass('tab-active');
            $('.tab-content__big-chart').removeClass('d-block').addClass('d-none');
            $(this).addClass('tab-active');

            var showTabId = $(this).attr('data-tab');

            $('#' + showTabId).removeClass('d-none').addClass('d-block');
        });
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js" charset="utf-8"></script>
{!! $closeRatioChart->script() !!}
{!! $meetingRatingChart->script() !!}
{!! $closeRatioBenchmarkChart->script() !!}
{!! $meetingRatingBenchmarkChart->script() !!}
@endsection