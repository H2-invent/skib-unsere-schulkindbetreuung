<?php

declare(strict_types=1);

namespace App\Dto\TemplatePreview;

use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Stammdaten;

/**
 * Unpersisted sample entities used to preview city-authored templates.
 *
 * These are never flushed, so nothing on them has an id and the Zeitblocks have neither a Schule nor an
 * Active. Anything a template needs beyond plain fields has to be passed alongside as scalars.
 */
final readonly class PreviewFixture
{
    public function __construct(
        public Stammdaten $eltern,
        public Kind $kind,
        public Organisation $organisation,
    ) {
    }
}
