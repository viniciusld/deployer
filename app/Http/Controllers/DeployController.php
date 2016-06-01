<?php

namespace App\Http\Controllers;

use App\Deploy;
use App\Jobs\DeploymentQueueJob;
use Illuminate\Http\Request;
use App\Libraries\GitLibrary;


use App\Http\Requests;
use Log;
use Storage;

class DeployController extends Controller
{

    public function index()
    {
        $branches = GitLibrary::branches();
        $servers = array_keys(config('remote.connections'));

        return view('main', compact('branches', 'servers'));
    }

    public function deployIt(Request $request)
    {
    	$deploy = new Deploy;
        $deploy->server = $request->input('server');
        $deploy->branch = $request->input('branch');
        $deploy->save();

    	$this->dispatch(new DeploymentQueueJob($deploy));
    	return redirect('/status');
    }

    public function deployCommand(Request $request){

        $commands = '';

        if(Storage::exists('deploy_command')){
            $commands = Storage::get('deploy_command');
        }

    	return view('command', compact('commands'));
    }

    public function saveCommand(Request $request){
        $commands = Storage::put('deploy_command', $request->input('command'));
        return redirect('/command');
    }

    public function status(Request $request)
    {
        $deploys = Deploy::with('outputs')->orderBy('updated_at', 'desc')->get();
        Log::info($deploys);
        return view('status', ['deploys' => $deploys]);
    }
}
