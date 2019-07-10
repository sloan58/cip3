@extends('vendor.backpack.base.layout')

@section('header')
    <section class="content-header">
    </section>
@endsection


@section('content')
    <div class="row">
        <div class="col-md-12">
            @include('app_settings::_settings')
        </div>
    </div>
@endsection
