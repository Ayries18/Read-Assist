<?php
header('Content-Type: text/plain');

echo "=== GIT STATUS ===\n";
echo shell_exec('git status 2>&1');

echo "\n=== GIT DIFF ===\n";
echo shell_exec('git diff 2>&1');
