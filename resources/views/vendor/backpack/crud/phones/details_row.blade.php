<div class="m-t-10 m-b-10 p-l-10 p-r-10 p-t-10 p-b-10">
    <div class="row">
        <div class="col-md-12">
            @if($entry->realtime_data[0]['Status'])
            <ul>
                <li><b>UCM Node:</b> {{ $entry->realtime_data[0]['UcmNode'] }}</li>
                <li><b>Status:</b> {{ $entry->realtime_data[0]['Status'] }}</li>
                @if($entry->realtime_data[0]['Status'] !== "Registered")
                    <li><b>Reason:</b> {{ $entry->realtime_data[0]['StatusReason'] }}</li>
                @endif
                <li><b>Protocol:</b> {{ $entry->realtime_data[0]['Protocol'] }}</li>
                <li><b>IP Address:</b> {{ $entry->realtime_data[0]['IPAddress'] }}</li>
                <li><b>Lines:</b></li>
                @if(is_array($entry->realtime_data[0]['Lines']))
                    <ul>
                        @foreach($entry->realtime_data[0]['Lines'] as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                @endif
                <li><b>Active Load:</b> {{ $entry->realtime_data[0]['ActiveLoadID'] }}</li>
                <li><b>Inactive Load:</b> {{ $entry->realtime_data[0]['InactiveLoadID'] }}</li>
                <li><b>Download Status:</b> {{ $entry->realtime_data[0]['DownloadStatus'] }}</li>
                @if($entry->realtime_data[0]['DownloadFailureReason'])
                <li><b>Download Failure Reason:</b> {{ $entry->realtime_data[0]['DownloadFailureReason'] }}</li>
                @endif
            </ul>

                <b>UCM Last Updated:</b> {{ \Carbon\Carbon::createFromTimestamp($entry->realtime_data[0]['UCMTimestamp'])->diffForHumans() }}<br>
                <b>CIP3 Last Sync:</b> {{ \Carbon\Carbon::createFromTimestamp($entry->realtime_data[0]['CIP3Timestamp'])->diffForHumans() }}
            @else
                No Realtime data for this device
            @endif
        </div>
    </div>
</div>
<div class="clearfix"></div>
