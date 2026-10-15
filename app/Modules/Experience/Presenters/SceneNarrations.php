<?php

namespace App\Modules\Experience\Presenters;

use App\Models\Spa;
use App\Models\SpaKnowledgeItem;
use App\Models\Treatment;
use App\Models\TreatmentRoom;
use Illuminate\Support\Collection;

/**
 * What the AI Spa Concierge says when the guest steps into a scene. Every sentence
 * is built from stored spa data, never generated.
 */
final class SceneNarrations
{
    /**
     * @param  Collection<int, Treatment>  $items
     * @return array{treatments: ?string, item: array<string, string>}
     */
    public static function treatments(Spa $spa, Collection $items, string $locale): array
    {
        $templates = StageCopy::narration($locale);
        $terms = StageCopy::terms($locale);
        $money = self::money($spa);
        $sentence = self::sentence($locale);
        $separator = $locale === 'ja' ? '、' : ', ';

        if ($items->isEmpty()) {
            return ['treatments' => null, 'item' => []];
        }

        $intro = str_replace(
            [':count', ':price'],
            [(string) $items->count(), $money($items->min('price'))],
            $templates[$items->count() === 1 ? 'treatments_one' : 'treatments']
        );

        $narrations = $items->mapWithKeys(function (Treatment $item) use ($templates, $terms, $money, $sentence, $separator, $locale) {
            $parts = [$item->translatedName($locale).'.', filled($item->translatedDescription($locale)) ? $sentence(trim($item->translatedDescription($locale))) : null];

            if ($item->duration_minutes) {
                $parts[] = str_replace(':duration_label', (string) $item->duration_minutes, $templates['item_duration_label']);
            }

            if (! empty($item->contraindications)) {
                $list = collect($item->contraindications)->map(fn ($precaution) => $terms['precaution'][$precaution] ?? $precaution)->implode($separator);
                $parts[] = str_replace(':list', $list, $templates['item_contraindications']);
            }

            $parts[] = str_replace(':price', $money($item->price), $templates['item_price']);

            if ($item->is_unavailable) {
                $parts[] = $templates['item_sold_out'];
            }

            return [$item->slug => implode(' ', array_filter($parts))];
        })->all();

        return ['treatments' => $intro, 'item' => $narrations];
    }

    /**
     * @param  Collection<int, TreatmentRoom>  $areas
     * @return array{rooms: ?string, area: array<string, string>}
     */
    public static function rooms(Spa $spa, Collection $areas, string $locale): array
    {
        $templates = StageCopy::narration($locale);
        $money = self::money($spa);
        $sentence = self::sentence($locale);

        if ($areas->isEmpty()) {
            return ['rooms' => null, 'area' => []];
        }

        $first = $areas->first()->translatedName($locale);
        $intro = str_replace(
            [':count', ':first'],
            [(string) $areas->count(), $first],
            $templates[$areas->count() === 1 ? 'rooms_one' : 'rooms']
        );

        $narrations = $areas->mapWithKeys(function (TreatmentRoom $area) use ($templates, $money, $sentence, $locale) {
            $parts = [$area->translatedName($locale).'.', filled($area->translatedDescription($locale)) ? $sentence(trim($area->translatedDescription($locale))) : null];

            $parts[] = $area->min_guests > 1
                ? str_replace([':min', ':max'], [(string) $area->min_guests, (string) $area->max_guests], $templates['seat_guests'])
                : str_replace(':max', (string) $area->max_guests, $templates['seat_guests_one']);

            $parts[] = (float) $area->reservation_fee > 0
                ? str_replace(':fee', $money($area->reservation_fee), $templates['seat_fee'])
                : $templates['seat_free'];

            if ($area->minimum_spend !== null && (float) $area->minimum_spend > 0) {
                $parts[] = str_replace(':spend', $money($area->minimum_spend), $templates['seat_spend']);
            }

            return [$area->slug => implode(' ', array_filter($parts))];
        })->all();

        return ['rooms' => $intro, 'area' => $narrations];
    }

    /**
     * Intros for the facilities list and the spa information scene, built
     * only from stored spa data.
     *
     * @param  Collection<int, SpaKnowledgeItem>  $facilities
     * @return array{facilities: ?string, info: string}
     */
    public static function scenes(Spa $spa, Collection $facilities, string $locale): array
    {
        $templates = StageCopy::narration($locale);
        $separator = $locale === 'ja' ? '、' : '; ';
        $time = fn (?string $value) => $value ? substr($value, 0, 5) : null;

        $intro = null;

        if ($facilities->isNotEmpty()) {
            $examples = $facilities->take(3)->map(fn ($item) => $item->translatedTitle($locale))->implode($separator);
            $intro = str_replace(
                [':count', ':examples'],
                [(string) $facilities->count(), $examples],
                $templates[$facilities->count() === 1 ? 'facilities_one' : 'facilities']
            );
        }

        $location = collect([$spa->city, $spa->country])->filter()->implode($locale === 'ja' ? '、' : ', ');
        $parts = [str_replace(':spa', $spa->name, $templates['info_welcome'])];

        if ($location !== '') {
            $parts[] = str_replace(':location', $location, $templates['info_place']);
        }

        if ($time($spa->opening_time) && $time($spa->closing_time)) {
            $parts[] = str_replace([':open', ':close'], [$time($spa->opening_time), $time($spa->closing_time)], $templates['info_hours']);
        }

        $parts[] = $templates['info_more'];

        return ['facilities' => $intro, 'info' => implode(' ', $parts)];
    }

    /**
     * @return \Closure(mixed): string
     */
    private static function money(Spa $spa): \Closure
    {
        return fn ($value) => $spa->currency.' '.number_format((float) $value, 0, ',', '.');
    }

    /**
     * @return \Closure(string): string
     */
    private static function sentence(string $locale): \Closure
    {
        return fn (string $text) => preg_match('/[.!?。！？]$/u', $text) ? $text : $text.($locale === 'ja' ? '。' : '.');
    }
}
