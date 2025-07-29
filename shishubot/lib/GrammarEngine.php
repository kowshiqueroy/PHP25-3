<?php

class GrammarEngine {
    private array $synonyms = [
        'define' => ['what is', 'tell me about', 'explain'],
        'remember' => ['teach', 'store', 'learn me'],
        'write' => ['generate', 'create'],
        'fix' => ['debug', 'improve'],
        'sort' => ['order'],
        'filter' => ['select from'],
        'solve' => ['equation', 'math'],
        'poem' => ['poetry'],
        'story' => ['tale'],
        'quote' => ['saying', 'maxim'],
        'metaphor' => ['analogy'],
    ];

    public function preprocessMessage(string $message): array {
        $message = strtolower(trim($message));
        $correctionsMade = []; // No corrections will be made in this simplified version

        // Apply synonym replacement (replace synonyms with their canonical form)
        foreach ($this->synonyms as $canonical => $syns) {
            foreach ($syns as $synonym) {
                // Ensure whole word replacement to avoid partial matches
                if (str_contains($message, $synonym)) {
                    $message = preg_replace('/\b' . preg_quote($synonym, '/') . '\b/', $canonical, $message);
                }
            }
        }

        return [
            'message' => $message,
            'corrections' => $correctionsMade
        ];
    }

    // These methods are now redundant as preprocessMessage handles it
    public function correctGrammar(string $text): string {
        return $text;
    }

    public function detectSynonyms(string $word): array {
        return [$word];
    }
}

?>