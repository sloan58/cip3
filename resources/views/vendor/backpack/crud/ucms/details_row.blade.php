<div class="m-t-10 m-b-10 p-l-10 p-r-10 p-t-10 p-b-10">
    <div class="row">
        <div class="col-md-3 m-b-10">
            Last 3 Sync Statuses:
        </div>
        <div class="col-md-12">
            <ul class="list-group">
            @forelse($entry->sync_history as $key => $history)
                @if($history['status'] == "Completed")
                <li class="list-group-item">
                    <a href="#" class="btn btn-success btn-sm">Success</a>
                    Sync Completed
                    <u class="text-success font-weight-bold"> Successfully</u>
                    on {{ \Carbon\Carbon::parse($history['timestamp'])->toDayDateTimeString() }}
                    ({{ \Carbon\Carbon::parse($history['timestamp'])->diffForHumans() }})
                </li>
                @else
                <li class="list-group-item">
                    <a href="#" class="btn btn-danger">Failed</a>
                    Sync Completed
                    <u class="text-danger font-weight-bold"> with Failure</u>
                    on {{ \Carbon\Carbon::parse($history['timestamp'])->toDayDateTimeString() }}
                    ({{ \Carbon\Carbon::parse($history['timestamp'])->diffForHumans() }}) <br>
                    <b>Error Code:</b> {{ $history['error_code'] }} <br>
                    <b>Error Message:</b> {{ $history['error_message'] }} <br>
                </li>
                @endif
            @empty
                <li class="list-group-item">No Sync Data Available</li>
            @endforelse
            </ul>
        </div>
    </div>
</div>
<div class="clearfix"></div>
