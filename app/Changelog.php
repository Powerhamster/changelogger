<?php
declare(strict_types=1);

namespace App;

class Changelog
{
    /** @var string */
    private $header;

    /** @var array<string, string> */
    private $releases = [];

    private function __construct(string $header)
    {
            $this->header = $header;
    }
    private function addRelease(string $release) : void
    {
        preg_match('/^## \[(.*)\].*\n\n/', $release, $matches);

        $version = $matches[1];

        $this->releases[$version] = $release;
    }

    public static function parse(string $markdown) : self
    {
        if (preg_match('/^.*<!-- CHANGELOGGER -->$\n\n/m', $markdown, $headerMatches) === 0) {
            throw new \Exception('Placeholder <!-- CHANGELOGGER --> is missing');
        }
        $header = &$headerMatches[0];
        $releasesContent = str_replace($header, '', $markdown);
        $changelog = new self($header);

        $releases = explode("\n\n\n", $releasesContent);

        foreach ($releases as $release) {
            $changelog->addRelease(trim($release));
        }

        return $changelog;
    }

    public function sort() : void
    {
        krsort($this->releases);
    }

    public function write() : string
    {
        return sprintf("%s%s\n", $this->header, implode("\n\n\n", $this->releases));
    }
}
