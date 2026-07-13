<?php

namespace App\Domains\ModulesFormateur\Support;

class DecoupeurBlocsLecon
{
    /**
     * Splits a lesson's flat content_blocks array into progressive reveal
     * segments (bounded by 'reveal'-mode divider blocks). 'simple'-mode
     * dividers (and legacy/unrecognized dividers, e.g. the removed
     * 'pagination' mode) are left untouched as regular blocks inside
     * whichever segment they land in.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<int, array<string, mixed>>> segments, each a list of blocks
     */
    public static function decouper(array $blocks): array
    {
        $rawSegments = self::splitOnRevealMode($blocks);

        $segments = [];
        foreach ($rawSegments as $i => $segmentBlocks) {
            if ($i > 0 && $segmentBlocks === []) {
                continue;
            }
            $segments[] = $segmentBlocks;
        }

        return $segments === [] ? [[]] : $segments;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<int, array<string, mixed>>>
     */
    private static function splitOnRevealMode(array $blocks): array
    {
        $groups = [[]];

        foreach ($blocks as $block) {
            if (($block['type'] ?? null) === 'divider' && ($block['mode'] ?? 'simple') === 'reveal') {
                $groups[] = [];
                continue;
            }
            $groups[count($groups) - 1][] = $block;
        }

        return $groups;
    }
}
