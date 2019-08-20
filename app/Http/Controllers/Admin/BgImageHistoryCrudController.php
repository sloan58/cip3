<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\BgImageHistoryRequest as StoreRequest;
use App\Http\Requests\BgImageHistoryRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;

/**
 * Class BgImageHistoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class BgImageHistoryCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\BgImageHistory');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/bgimage-history');
        $this->crud->setEntityNameStrings('Background Push History', 'Background Push Histories');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        // TODO: remove setFromDb() and manually define Fields and Columns
        $this->crud->addColumn(
            [
                'name' => 'screenshot',
                'label' => "Screen Shot",
                'type' => 'view',
                'view' => 'vendor.backpack.crud.columns.bgImageHistories.screenshot',
            ]
        );
        $this->crud->setFromDb();
        $this->crud->addColumn(
            [
                'name' => "updated_at", // The db column name
                'label' => "Updated At", // Table column heading
                'type' => "datetime",
                // 'format' => 'l j F Y H:i:s', // use something else than the base.default_datetime_format config value
            ]
        );

        // Remove buttons
        $this->crud->removeButtonFromStack('update', 'line');

        // add asterisk for fields that are required in BgImageHistoryRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
    }

    public function store(StoreRequest $request)
    {
        // your additional operations before save here
        $redirect_location = parent::storeCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
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
