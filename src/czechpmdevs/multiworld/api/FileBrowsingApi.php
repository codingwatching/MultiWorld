<?php

/**
 * MultiWorld - PocketMine plugin that manages worlds.
 * Copyright (C) 2018 - 2021  CzechPMDevs
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */


declare(strict_types=1);

namespace czechpmdevs\multiworld\api;

use Generator;
use function glob;
use function is_dir;

class FileBrowsingApi {

    /**
     * Returns all subdirectories in the path
     *
     * @return string[]
     */
    public static function getAllSubdirectories(string $dir): array {
        $scanDirectory = function (string $dir): Generator {
            if($subDirs = glob($dir . "/*")) {
                foreach ($subDirs as $subDir) {
                    if (is_dir($subDir)) {
                        yield $subDir;
                    }
                }
            }
        };

        $all = [];
        $toCheck = [$dir => 0];

        check:
        foreach (array_keys($toCheck) as $scanning) {
            foreach ($scanDirectory($scanning) as $subDirectory) {
                if (!in_array($subDirectory, $all)) {
                    $all[] = $subDirectory;
                    $toCheck[$subDirectory] = 0;
                }
            }

            unset($toCheck[$scanning]);
        }

        if (!empty($toCheck)) {
            goto check;
        }

        return $all;
    }

    /**
     * Saves resources with subdirectories
     */
    public static function saveResource(string $sourceFile, string $targetFile, bool $rewrite = false): bool {
        if (file_exists($targetFile)) {
            if ($rewrite) {
                unlink($targetFile);
            } else {
                return false;
            }
        }

        $dirs = explode(DIRECTORY_SEPARATOR, $targetFile);
        $file = array_pop($dirs);

        $tested = "";
        foreach ($dirs as $dir) {
            $tested .= $dir . DIRECTORY_SEPARATOR;
            if (!file_exists($tested)) {
                @mkdir($tested);
            }
        }

        file_put_contents($targetFile, file_get_contents($sourceFile));
        return true;
    }


    /**
     * Example:
     * $path = 'D:\JetBrains\PhpstormProjects\ProjectCzechPMDevs\plugins\MultiWorld/resources/structures/village/snowy/houses';
     * $root = 'resources'
     * -> '/structures/village/snowy/houses'
     */
    public static function removePathFromRoot(string $path, string $root): string {
        $position = strpos($path, $root);
        if ($position === false) {
            return $path;
        }

        return substr($path, $position + strlen($root));
    }
}