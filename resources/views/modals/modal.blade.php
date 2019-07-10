<!-- Export Phones Modal -->
<div class="modal fade" id="exportPhones" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Modal title</h4>
            </div>
            <form method="POST" action="{{ backpack_url('report') }}">
                @csrf
                <div class="modal-body">
                        <div class="form-group">
                            <label for="ucms">UCM System(s)</label>
                            <select class="form-control" id="ucms" name="ucms">
                                <option>All</option>
                                @foreach($ucms as $ucm)
                                <option>{{ $ucm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    <div class="form-group">
                        <label for="tag">Friendly Tag</label>
                        <input type="text" class="form-control" id="tag" name="tag" placeholder="Some meaningful tag...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
