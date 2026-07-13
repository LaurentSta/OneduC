<?php

use App\Domains\ModulesFormateur\Support\DecoupeurBlocsLecon;

function block(string $type, array $extra = []): array
{
    return array_merge(['type' => $type], $extra);
}

it('returns a single segment when there are no reveal dividers', function () {
    $blocks = [block('text', ['html' => 'a']), block('divider', ['mode' => 'simple']), block('text', ['html' => 'b'])];

    $segments = DecoupeurBlocsLecon::decouper($blocks);

    expect($segments)->toHaveCount(1)
        ->and($segments[0])->toBe($blocks);
});

it('splits into reveal segments on each reveal divider', function () {
    $blocks = [
        block('text', ['html' => 'base']),
        block('divider', ['mode' => 'reveal']),
        block('text', ['html' => 'chunk1']),
        block('divider', ['mode' => 'reveal']),
        block('text', ['html' => 'chunk2']),
    ];

    $segments = DecoupeurBlocsLecon::decouper($blocks);

    expect($segments)->toHaveCount(3)
        ->and($segments[0])->toBe([block('text', ['html' => 'base'])])
        ->and($segments[1])->toBe([block('text', ['html' => 'chunk1'])])
        ->and($segments[2])->toBe([block('text', ['html' => 'chunk2'])]);
});

it('drops a trailing reveal divider with nothing to reveal', function () {
    $segments = DecoupeurBlocsLecon::decouper([block('text', ['html' => 'a']), block('divider', ['mode' => 'reveal'])]);
    expect($segments)->toHaveCount(1);
});

it('handles a reveal divider as the very first block', function () {
    $segments = DecoupeurBlocsLecon::decouper([block('divider', ['mode' => 'reveal']), block('text', ['html' => 'a'])]);

    expect($segments)->toHaveCount(2)
        ->and($segments[0])->toBe([])
        ->and($segments[1])->toBe([block('text', ['html' => 'a'])]);
});

it('collapses consecutive reveal dividers to a single boundary', function () {
    $segments = DecoupeurBlocsLecon::decouper([
        block('divider', ['mode' => 'reveal']),
        block('divider', ['mode' => 'reveal']),
        block('text', ['html' => 'a']),
    ]);

    expect($segments)->toHaveCount(2)
        ->and($segments[0])->toBe([])
        ->and($segments[1])->toBe([block('text', ['html' => 'a'])]);
});

it('treats simple and legacy/unrecognized (e.g. the removed pagination) dividers as plain content, not boundaries', function () {
    $blocks = [
        block('text', ['html' => 'a']),
        block('divider'),
        block('divider', ['mode' => 'pagination']),
        block('text', ['html' => 'b']),
    ];

    $segments = DecoupeurBlocsLecon::decouper($blocks);

    expect($segments)->toHaveCount(1)
        ->and($segments[0])->toBe($blocks);
});
