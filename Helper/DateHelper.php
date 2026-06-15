<?php

namespace NyroDev\UtilityBundle\Helper;

use DateTimeInterface;
use NyroDev\UtilityBundle\Helper\Interfaces\StartEndInterface;
use NyroDev\UtilityBundle\Services\AbstractService;
use NyroDev\UtilityBundle\Services\NyrodevService;

class DateHelper extends AbstractService
{
    public static function getAlias()
    {
        return 'date';
    }

    public function __construct(
        private readonly NyrodevService $nyrodev,
    ) {
    }

    public function format(DateTimeInterface $date, string $format = 'date.short'): string
    {
        return $this->nyrodev->formatDate($date, $format);
    }

    public function formatStartEnd(DateTimeInterface $start, ?DateTimeInterface $end = null, bool $shortenTranslationIfPossible = false): string
    {
        if (!$end || $end->format('Ymd') === $start->format('Ymd')) {
            $startText = $this->format($start, 'date.shortMonthText');
            if ($shortenTranslationIfPossible) {
                return $startText;
            }

            return $this->nyrodev->trans('date.startEnd.singleDay', [
                '%start%' => $startText,
            ]);
        }

        if ($start->format('Ym') === $end->format('Ym')) {
            $startText = $start->format('d');
        } else {
            $startText = $this->format($start, 'date.shortMonthNoYearText');
        }

        $endText = $this->format($end, 'date.shortMonthText');

        $transKey = 'date.startEnd.regular';

        if ($shortenTranslationIfPossible && 1 === $start->diff($end)->d) {
            $transKey = 'date.startEnd.consecutiveDays';
        }

        return $this->nyrodev->trans($transKey, [
            '%start%' => $startText,
            '%end%' => $endText,
        ]);
    }

    public function formatStartEndObject(StartEndInterface $object, bool $shortenTranslationIfPossible = false): string
    {
        if (!$object->getStartDate()) {
            return '';
        }

        return $this->formatStartEnd($object->getStartDate(), $object->getEndDate(), $shortenTranslationIfPossible);
    }
}
