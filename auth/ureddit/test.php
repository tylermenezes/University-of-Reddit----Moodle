<?php

header("Content-type: text/plain");
        $postdata = http_build_query(
            array(
                
                'username' => "tylermenezes",
                'password' => "Neocymium!"
            )
        );

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );
        var_dump($postdata);
        $opts  = stream_context_create($opts);


        exit;