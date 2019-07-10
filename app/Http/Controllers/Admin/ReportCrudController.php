<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\ProcessPhoneReportJob;
use Carbon\Carbon;
use Backpack\CRUD\CrudPanel;
use App\Http\Requests\ReportRequest as StoreRequest;
use App\Http\Requests\ReportRequest as UpdateRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;

/**
 * Class ReportCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class ReportCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Report');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/report');
        $this->crud->setEntityNameStrings('report', 'reports');

        // Custom Buttons
        $this->crud->removeAllButtonsFromStack('line');
        $this->crud->addButtonFromView('line', 'download', 'phone_report_download', 'end');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        // TODO: remove setFromDb() and manually define Fields and Columns
        $this->crud->setFromDb();

        // add asterisk for fields that are required in ReportRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
    }

    public function store(StoreRequest $request)
    {
        if($request->request->get('tag')) {
            $tag = preg_replace('/\'/', '', $request->request->get('tag'));
            $tag = preg_replace('/\s/', '_', $tag) . '_';
        } else {
            $tag = '';
        }
        $timestamp = Carbon::now()->timestamp;
        $filename = "{$tag}{$timestamp}_cip3_phone_report.csv";

        $request->request->add([
            'type' => 'phone',
            'filename' => $filename,
            'submitted_by' => backpack_user()->email,
            'status' => 'processing'
        ]);

        // your additional operations before save here
        $redirect_location = parent::storeCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry

        ProcessPhoneReportJob::dispatch(
            $this->crud->entry,
            $request->request->get('ucms')
        );

        return $redirect_location;
    }

    public function update(UpdateRequest $request)
    {
        // your additional operations before save here
        $redirect_location = parent::updateCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
    }
}
