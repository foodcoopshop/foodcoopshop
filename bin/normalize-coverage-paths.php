<?php
declare(strict_types=1);

$coverageFile = $argv[1] ?? null;

if (!is_string($coverageFile) || $coverageFile === '') {
    fwrite(STDERR, "Usage: php bin/normalize-coverage-paths.php <coverage-file>\n");
    exit(1);
}

if (!file_exists($coverageFile)) {
    fwrite(STDERR, sprintf("Coverage file not found: %s\n", $coverageFile));
    exit(1);
}

$document = new DOMDocument();
$document->preserveWhiteSpace = true;
$document->formatOutput = true;

if (!$document->load($coverageFile)) {
    fwrite(STDERR, sprintf("Could not load coverage file: %s\n", $coverageFile));
    exit(1);
}

$xpath = new DOMXPath($document);
$sourceNodes = $xpath->query('/coverage/sources/source');

if ($sourceNodes === false) {
    fwrite(STDERR, "Could not query coverage sources.\n");
    exit(1);
}

foreach ($sourceNodes as $sourceNode) {
    $sourceValue = trim($sourceNode->textContent);
    if ($sourceValue !== '' && str_starts_with($sourceValue, '/')) {
        $sourceNode->nodeValue = '.';
    }
}

if ($document->save($coverageFile) === false) {
    fwrite(STDERR, sprintf("Could not save coverage file: %s\n", $coverageFile));
    exit(1);
}