<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Console\OutputStyle;
use Illuminate\Support\MessageBag;

class GitHubActionsOutput extends MessageBag
{
    public function render(OutputStyle $output): void
    {
        foreach ($this->messages() as $key => $message) {
            $value = head($message);

            if ($this->hasGithubOutputEnvironment()) {
                $this->setOutput($key, $value);
            }

            // Set output variables using old syntax for compatibility with older versions of GitHub Actions runner.
            // Stops working in 2023.
            if (now()->year < 2023) {
                $output->text(sprintf("::set-output name=%s::%s", $key, $value));
            }
        }
    }

    private function setOutput($name, $value): void
    {
        $pathToGitHubOutput = getenv('GITHUB_OUTPUT');
        $gitHubOutput = file_get_contents($pathToGitHubOutput);

        $gitHubOutput .= "$name=$value\n";

        file_put_contents($pathToGitHubOutput, $gitHubOutput, FILE_APPEND | LOCK_EX);
    }

    private function hasGithubOutputEnvironment(): bool
    {
        $gitHubOutput = getenv('GITHUB_OUTPUT');

        return ! empty($gitHubOutput);
    }
}
