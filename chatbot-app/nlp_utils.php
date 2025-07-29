<?php
/**
 * Simple tokenizer: lowercase, strip non-word, split spaces
 */
function tokenize(string $text): array {
    $text = mb_strtolower($text, 'UTF-8');
    // Remove non-letters/numbers
    $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);
    // Split and filter
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    return $words ?: [];
}