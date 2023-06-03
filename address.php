<?php
//get_address();
//function get_address(){
    $command = './openethereum account new --config nodes/validator/node.toml';
    $descriptors = array(
        0 => array('pipe', 'r'),  // stdin
        1 => array('pipe', 'w'),  // stdout
        2 => array('pipe', 'w'),  // stderr
    );

    $process = proc_open($command, $descriptors, $pipes);

        if (is_resource($process)) {
        // Read output from stdout and stderr
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        // Close the process
        $exitCode = proc_close($process);

        // Display output and errors

        $result['Output'] = $output;
        $result['Errors'] = $errors;
        $result['Exit'] = $exitCode;

        print_r($output);
       // print_r(array('variable' => $result, 'content' => $output));
        return array('variable' => $result, 'content' => $output);
        //return $result;

        /* //print_r($result);
         echo '-------' . '<br>';
         echo "Output: " . $output . "<br>";
         echo "Errors: " . $errors . "<br>";
         echo "Exit code: " . $exitCode . "<br>";*/
    }
//    return array('variable' => 'null', 'content' => 'null');
//    }
?>