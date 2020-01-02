<?php

namespace Folklore\EloquentJson\Nodes;

use Folklore\EloquentJson\Contracts\Node as NodeContract;

class Node implements NodeContract
{
    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function path()
    {
        return $this->path;
    }

    public function isPath($path)
    {
        $pathPattern = preg_replace(
            '/(\(\\\.\.\+\))$/',
            '$1?',
            str_replace(
                ['\.\*', '\*'],
                ['(\..+)', '([^\.]+)'],
                preg_quote($path)
            )
        );
        return preg_match('/^' . $pathPattern . '$/', $this->path);
    }

    public function prependPath($path)
    {
        $this->path = $path . '.' . $this->path;
        return $this;
    }

    public function removePath($path)
    {
        $this->path = preg_replace(
            '/^' . preg_quote($path) . '(\.(.+))?$/',
            '$2',
            $this->path
        );
        return $this;
    }
}
