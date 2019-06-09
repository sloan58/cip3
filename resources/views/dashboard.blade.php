@extends('layout')

@section('header')
    <section class="content-header">
    </section>
@endsection


@section('content')
    <div class="row">
        <div class="col-md-12">
            @if(isset($noData))
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">UCM Phone Statistics</h3>
                </div>
                <div class="panel-body text-center">
                    <h1><a href="{{ route('crud.ucm.index') }}">Add a UCM Server</a>
                        <br>to Collect Statistics</h1>
                </div>
            </div>
            @else
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">UCM Phone Statistics</h3>
                </div>
                <div class="panel-body">
                    <div class="col-md-6">
                        {!! $phoneModels->render() !!}
                    </div>
                    <div class="col-md-6">
                        {!! $clusterCounts->render() !!}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
