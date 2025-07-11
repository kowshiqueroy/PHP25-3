<?php
$__stdin = '5';
$__pos   = 0;
function readToken() {
    global $__stdin, $__pos;
    while (isset($__stdin[$__pos]) && ctype_space($__stdin[$__pos])) {
        $__pos++;
    }
    if (!isset($__stdin[$__pos])) return false;
    $start = $__pos;
    while (isset($__stdin[$__pos]) && !ctype_space($__stdin[$__pos])) {
        $__pos++;
    }
    return substr($__stdin, $start, $__pos - $start);
}
// begin translated code
$a = 0;
$b = 10;
$a = (int) readToken();
for ($int $i = 0; $i < $a; $i++) {
$b = b + i;
echo sprintf("Result = %d\n", b);
// end translated code
?>