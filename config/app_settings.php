<?php

return [

    // All the sections for the settings page
    'sections' => [
        'app' => [
            'title' => 'Webex Teams',
            'descriptions' => 'Webex Teams Bot Account Integration', // (optional)
            'icon' => 'fa fa-comments-o', // (optional)

            'inputs' => [
                [
                    'name' => 'teams_token', // unique key for setting
                    'type' => 'text', // type of input can be text, number, textarea, select, boolean, checkbox etc.
                    'label' => 'Webex Teams Bot Token', // label for input
                    // optional properties
                    'placeholder' => 'Your Webex Teams Bot Token', // placeholder for input
                    'class' => 'form-control', // override global input_class
                    'style' => '', // any inline styles
                    'rules' => '', // validation rules for this input
                    'value' => '', // any default value
                    'hint' => 'Get this from your Cisco Webex developer account' // help block text for input
                ],
                [
                    'name' => 'teams_to_address', // unique key for setting
                    'type' => 'text', // type of input can be text, number, textarea, select, boolean, checkbox etc.
                    'label' => 'Webex Teams message recipient', // label for input
                    // optional properties
                    'placeholder' => 'you@somecorp.com', // placeholder for input
                    'class' => 'form-control', // override global input_class
                    'style' => '', // any inline styles
                    'rules' => '', // validation rules for this input
                    'value' => '', // any default value
                    'hint' => 'The Bot will send Teams messages to this account' // help block text for input
                ],
                [
                    'name' => 'teams_enable_notifications', // unique key for setting
                    'type' => 'checkbox', // type of input can be text, number, textarea, select, boolean, checkbox etc.
                    'label' => 'Enable Notifications', // label for input
                    // optional properties
                    'placeholder' => '', // placeholder for input
                    'class' => 'form-control', // override global input_class
                    'style' => '', // any inline styles
                    'rules' => '', // validation rules for this input
                    'value' => '1',
                    'hint' => 'Enable/Disable Teams Notifications' // help block text for input
                ]
            ]
        ],
    ],

    // Setting page url, will be used for get and post request
    'url' => '/admin/settings',

    // Any middleware you want to run on above route
    'middleware' => [],

    // View settings
    'setting_page_view' => 'vendor.settings', // 'app_settings::settings_page'
    'flash_partial' => 'app_settings::_flash',

    // Setting section class setting
    'section_class' => 'card mb-3',
    'section_heading_class' => 'card-header',
    'section_body_class' => 'card-body',

    // Input wrapper and group class setting
    'input_wrapper_class' => 'form-group',
    'input_class' => 'form-control',
    'input_error_class' => 'has-error',
    'input_invalid_class' => 'is-invalid',
    'input_hint_class' => 'form-text text-muted',
    'input_error_feedback_class' => 'text-danger',

    // Submit button
    'submit_btn_text' => 'Save Settings',
    'submit_success_message' => 'Settings has been saved.',

    // Remove any setting which declaration removed later from sections
    'remove_abandoned_settings' => false,

    // Controller to show and handle save setting
    'controller' => '\QCod\AppSettings\Controllers\AppSettingController',
];
