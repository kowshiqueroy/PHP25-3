<?php
// nlp_utils.php

/**
 * Clean, lowercase and split text into an array of words.
 */
function tokenize(string $text): array {
    // remove punctuation, lowercase, split on spaces
    $clean = preg_replace('/[^\p{L}\p{N}\s]/u', '', mb_strtolower($text));
    return array_filter(explode(' ', $clean));
}