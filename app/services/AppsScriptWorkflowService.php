<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AppsScriptWorkflowService
{

    public function sendWorkflow($payload)
    {

        $url = env('APPS_SCRIPT_URL');


        return Http::post($url,[
            "action"=>"laravel_bridge",
            "payload"=>base64_encode(
                json_encode($payload)
            )
        ]);

    }

}