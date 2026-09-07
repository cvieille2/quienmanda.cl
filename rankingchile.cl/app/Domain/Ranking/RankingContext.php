<?php

namespace App\Domain\Ranking;

use App\Enums\RankingPeriodWindow;

final readonly class RankingContext
{
    public function __construct(
        public RankingPeriodWindow $period = RankingPeriodWindow::WEEK,
        public ?int $categoryId = null,
    ) {}

    public function cacheScope(): string
    {
        return $this->categoryId === null ? 'all' : 'category-' . $this->categoryId;
    }
}
