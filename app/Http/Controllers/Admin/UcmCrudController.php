<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\UcmRequest as StoreRequest;
use App\Http\Requests\UcmRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;

/**
 * Class UcmCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class UcmCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Ucm');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/ucm');
        $this->crud->setEntityNameStrings('ucm', 'ucms');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        $this->crud->setColumns([
            'Name',
            'IP Address',
            'Username',
        ]);
        
        $this->crud->addFields([
            [
                'name' => 'name',
                'type' => 'text',
                'label' => "UCM name"
            ],
            [
                'name' => 'ip_address',
                'type' => 'text',
                'label' => "UCM IP Address"
            ],
            [
                'name' => 'username',
                'type' => 'text',
                'label' => "Username"
            ],
            [
                'name' => 'password',
                'type' => 'password',
                'label' => "Password"
            ]
        ]);

        // add asterisk for fields that are required in UcmRequest
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
