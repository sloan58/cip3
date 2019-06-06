@extends('layout')

@section('header')
    <section class="content-header">
    </section>
@endsection


@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">UCM Phone Statistics</h3>
                </div>
                <div class="panel-body">
                    <div class="col-md-4">
                        {!! $phoneModels->render() !!}
                    </div>
                    <div class="col-md-4">
                        {!! $demoChart2->render() !!}
                    </div>
                    <div class="col-md-4">
                        {!! $demoChart3->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
