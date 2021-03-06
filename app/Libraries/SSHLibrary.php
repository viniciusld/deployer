<?php

namespace App\Libraries;

use SSH;
use Config;
use App\Models\Server;

class SSHLibrary
{
    private static $server;

    public static function server($server)
    {
        static::$server = $server;

        Config::set('remote.connections.'.$server->name, [
            'host' => $server->host,
            'username' => $server->username,
            'password' => $server->password,
            'key' => $server->key,
            'keytext' => $server->keytext,
            'keyphrase' => $server->keyphrase,
            'agent' => $server->agent,
            'timeout' => $server->timeout,
            'path' => $server->path
        ]);
    }

    public static function run(array $deploy_commands, callable $callback = null){

        if(!isset(static::$server)){
            static::server(Server::latest()->first());
        }

        $path = static::$server->path;

        array_unshift($deploy_commands, 'cd '. $path);

        SSH::into(static::$server->name)->run($deploy_commands, $callback);
    }
}
