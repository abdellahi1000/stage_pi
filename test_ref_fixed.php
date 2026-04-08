<?php
$all_requests = [
    ['id' => 1, 'type' => 'student'],
    ['id' => 2, 'type' => 'student'],
    ['id' => 3, 'type' => 'student']
];

foreach ($all_requests as &$req) {
    $req['thread'] = "thread_" . $req['id'];
}
unset($req);

$student_requests = array_filter($all_requests, function($r) { return $r['type'] === 'student'; });

foreach ($student_requests as $req) {
    echo $req['id'] . " ";
}
