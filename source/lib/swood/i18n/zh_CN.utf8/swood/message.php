<?php
return [
    'launcher_cli_example'  => "example: swood [options].. <command>",
    'launcher_cli_command'  => "
command:
    start       server run
    stop        server halt
    reload      server reload
    status      report all process status
    hold        change server into maintain mode
    call        call server api
    exec        execute a cli action
",
    'launcher_cli_options'     => "
options:
    -C <work_dir>       change work dir, default is current dir '.'
    -H <header_str>     header data str, could be JSON or http query
    -conf <conf>        current scene conf, default is 'default'
    -debug <level>      set debug level, default is 0
",

    'launcher_cli_call_example'     => "example: swood [option].. call <action> [params]..",
    'launcher_cli_call_options'     => "
    -port <port_id>     call port id, default is 0
    -ver <version>      call api version
    -app <app_name>     app name, default is first app in 'swood/apps.yml'
",
];
