@extends('layout')
@section('title', 'Login')

@section('links')
<!-- Fonts -->
<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
@endsection

@section('styles')
<style>
    .row {
        height: 100%;
    }
</style>

@endsection

@section('content')
<div class="row">
    <div class="col col-sm-4 offset-sm-4 align-self-center">
        <div class="accordion" id="accordionExample">
            <div class="card">
                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                    <div class="card-body">
                        <a id="login-with-google" class="btn btn-lg btn-danger btn-block" data-target="#collapseTwo">
                            <span id="google-login-verb">Login</span> with <span class="fa fa-google"></span>
                        </a>
                        <br />
                        <a id="login-with-slack" class="btn btn-lg btn-dark btn-block" href="" data-target="#collapseTwo">
                            <span id="slack-login-verb">Login</span> with <span class="fa fa-slack"></span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card">
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                    <div class="card-body">
                        <label for="slug">Your <b>slug</b></label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon3">https://</span>
                            </div>
                            <input name="slug" type="text" class="form-control" id="slug" aria-describedby="basic-addon3">
                            <div class="input-group-append">
                                <span class="input-group-text" id="basic-addon3">.soapboxhq.com</span>
                            </div>
                        </div>
                        <br />
                        <div class="row">
                            <div class="col-sm-4">
                                <button class="btn btn-lg btn-dark btn-block" data-toggle="collapse" data-target="#collapseOne">
                                    Go <span class="fa fa-arrow-left"></span>
                                </button>
                            </div>
                            <div class="col-sm-8">
                                <button id="login" class="btn btn-lg btn-primary btn-block">
                                    <span class="fa fa-spinner fa-spin" style="display: none;" id="login-in"></span> Finish <span class="fa fa-rocket"></span>
                                </button>
                            </div>
                        </div>
                        <br />
                        <div class="row" id="error-message" style="display: none;">
                            <div class="col-sm-12">
                                <div class="alert alert-danger" role="alert">
                                    <span id="error-message-text">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script src="{{ asset('js/Google.js') }}"></script>
<script src="{{ asset('js/Slack.js') }}"></script>
<script src="{{ asset('js/Login.js') }}"></script>
</script>
@endsection