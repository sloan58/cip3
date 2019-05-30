<div class="m-t-10 m-b-10 p-l-10 p-r-10 p-t-10 p-b-10">
    <div class="row">
        <div class="col-md-12">
            @if($entry->realtime_data[0]['Status'])
            <ul>
                <li><b>Status:</b> {{ $entry->realtime_data[0]['Status']}}</li>
                @if($entry->realtime_data[0]['Status'] !== "Registered")
                    <li><b>Reason:</b> {{ $entry->realtime_data[0]['StatusReason']}}</li>
                @endif
                <li><b>Protocol:</b> {{ $entry->realtime_data[0]['Protocol']}}</li>
                <li><b>IP Address:</b> {{ $entry->realtime_data[0]['IPAddress']}}</li>
                <li><b>Lines:</b> {{ $entry->realtime_data[0]['NumOfLines']}}</li>
                <li><b>Active Load:</b> {{ $entry->realtime_data[0]['ActiveLoadID']}}</li>
                <li><b>Inactive Load:</b> {{ $entry->realtime_data[0]['InactiveLoadID']}}</li>
                <li><b>Download Status:</b> {{ $entry->realtime_data[0]['DownloadStatus']}}</li>
                <li><b>Download Failure Reason:</b> {{ $entry->realtime_data[0]['DownloadFailureReason']}}</li>
            </ul>
            @else
                No Current Data for this device
            @endif
        </div>
    </div>
</div>
<div class="clearfix"></div>
