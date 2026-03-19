<?php
echo "step 1: start\n";

if (!session_start()) {
    echo "step 2: session_start failed\n";
} else {
    echo "step 2: session_start success\n";
}

// require_once('config/db_connect.php');
echo "step 3: db_connect skipped\n";

if (isset($conn)) {
    echo "step 4: conn exists\n";
} else {
    echo "step 4: conn does NOT exist\n";
}
