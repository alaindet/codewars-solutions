<?php

// https://www.codewars.com/kata/basic-nico-variation/train/php

/**
 * Turns a string into a numeric one (still a string) with these rules
 * 1. Store a 1-based index of all letters (original indexing)
 * 2. Sort the string alphabetically
 * 3. Store a new 1-based index of all letters (alphabetical indexing)
 * 4. Sort the string back to the original sorting
 * 5. Substitute every letter with its alphabetical index
 * 
 * Ex.: "crazy" => "23154"
 *
 * @param string $key
 * @return string Numerical key (see explanation above)
 */
function getNumericKey(string $key): string
{
    $letters = str_split($key);

    // Read and store original position indices
    $oldPosition = 1;
    $letters = array_map(function ($letter) use (&$oldPosition) {
        return [
            "letter" => $letter,
            "old-position" => $oldPosition++
        ];
    }, $letters);

    // Sort alphabetically
    usort($letters, function ($a, $b) {
        if ($a['letter'] < $b['letter']) return -1;
        if ($a['letter'] > $b['letter']) return +1;
        if ($a['letter'] === $b['letter']) return 0;
    });

    // Read and store alphabetical indices
    $newPosition = 1;
    $letters = array_map(function ($letter) use (&$newPosition) {
        $letter["new-position"] = $newPosition++;
        return $letter;
    }, $letters);

    // Sort back to original
    usort($letters, function ($a, $b) {
        return $a["old-position"] - $b["old-position"];
    });

    // Assemble the numerical key using alphabetical indices
    $numericKey = array_reduce($letters, function ($result, $letter) {
        return $result .= $letter["new-position"];
    }, "");

    return $numericKey;
}

/**
 * Encodes a message using a numerical key generated by getNumericalKey()
 *
 * @param string $message
 * @param string $numericKey
 * @return string Encoded message
 */
function encodeMessage(string $message, string $numericKey): string
{
    // Split message into same-length lines
    // Fill final line with whitespace if necessary
    $lines = [];
    $lineLength = strlen($numericKey);
    $linesCount = ceil(strlen($message) / $lineLength);
    $lineCurrent = "";
    $lineCurrentLength = 0;

    for ($i = 0, $ii = $linesCount * $lineLength; $i < $ii; $i++) {
        $lineCurrent .= $message[$i] ?? " ";
        $lineCurrentLength++;
        if ($lineCurrentLength === $lineLength) {
            $lines[] = $lineCurrent;
            $lineCurrent = "";
            $lineCurrentLength = 0;
        }
    }

    // Encode every line
    $encodedLines = [];
    for ($i = 0, $ii = count($lines); $i < $ii; $i++) {

        // Assign new positions to the letters according to the secret key
        $letters = [];
        for ($j = 0, $jj = $lineLength; $j < $jj; $j++) {
            $letter = $lines[$i][$j];
            $newPosition = $numericKey[$j];
            $letters[] = [
                "letter" => $letter,
                "new-position" => $newPosition,
            ];
        }

        // Sort the letters with the new positions
        usort($letters, function ($a, $b) {
            return intval($a["new-position"]) - intval($b["new-position"]);
        });

        // Collapse shuffled letters into new encoded line
        $encodedLines[] = implode("", array_column($letters, "letter"));
    }

    // Glue every encoded line together
    return implode("", $encodedLines);
}

/**
 * Encodes a message
 * 
 * @param string $key
 * @param string $message
 * @return string
 */
function nico(string $key, string $message): string
{
    $numericKey = getNumericKey($key);
    return encodeMessage($message, $numericKey);
}

// TEST
$tests = [
 [
    "expected" => "cseerntiofarmit on  ",
    "assertion" => nico("crazy", "secretinformation")
 ],
 [
    "expected" => "abcd  ",
    "assertion" => nico("abc", "abcd")
 ],
 [
    "expected" => "2143658709",
    "assertion" => nico("ba", "1234567890")
 ],
 [
    "expected" => "message",
    "assertion" => nico("a", "message")
 ],
 [
    "expected" => "eky",
    "assertion" => nico("key", "key")
 ],
];

$counter = 0;
$log = array_reduce($tests, function ($log, $test) use (&$counter) {

    $counter++;

    ($test["expected"] === $test["assertion"])
        ? $outcome = "PASSED"
        : $outcome = "NOT PASSED";

    return $log .= implode("\n", [
        "Test #" . $counter,
        "Assert: {$test["assertion"]}",
        "Expected: {$test["expected"]}",
        "Outcome: {$outcome}",
        "\n",
    ]);

}, "\n");

echo $log;
