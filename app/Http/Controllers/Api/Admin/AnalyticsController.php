<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Numbers and chart series for the admin dashboard overview.
 * Read-only: nothing here writes to the database.
 */
class AnalyticsController extends Controller
{
    /** Length of the "recent" window used by the charts and the trend arrows. */
    private const DAYS = 30;

    public function index()
    {
        $since = CarbonImmutable::now()->subDays(self::DAYS - 1)->startOfDay();
        $previousSince = $since->subDays(self::DAYS);

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'days' => self::DAYS,
            'kpis' => [
                'properties' => [
                    'total' => Property::count(),
                    'published' => Property::where('status', 'published')->count(),
                    'pending' => Property::where('status', 'pending')->count(),
                    'rejected' => Property::where('status', 'rejected')->count(),
                    'featured' => Property::where('status', 'published')->where('is_featured', true)->count(),
                    'views' => (int) Property::sum('views_count'),
                ],
                'users' => [
                    'total' => User::count(),
                    'active' => User::where('is_active', true)->count(),
                    'recent' => User::where('created_at', '>=', $since)->count(),
                    'previous' => User::whereBetween('created_at', [$previousSince, $since])->count(),
                    'pending_brokers' => User::where('broker_verification_status', 'pending')->count(),
                ],
                'inquiries' => [
                    'total' => Inquiry::count(),
                    'unanswered' => Inquiry::where('status', 'new')->count(),
                    'recent' => Inquiry::where('created_at', '>=', $since)->count(),
                    'previous' => Inquiry::whereBetween('created_at', [$previousSince, $since])->count(),
                ],
            ],
            'inquiries_by_day' => $this->dailySeries('inquiries', $since),
            'users_by_day' => $this->dailySeries('users', $since),
            'properties_by_status' => $this->groupedCounts(Property::query(), 'status', ['published', 'pending', 'rejected', 'archived']),
            'inquiries_by_status' => $this->groupedCounts(Inquiry::query(), 'status', ['new', 'contacted', 'closed']),
            'users_by_role' => $this->usersByRole(),
            'properties_by_type' => $this->propertiesByLookup('property_types', 'type_id'),
            'properties_by_city' => $this->propertiesByLookup('cities', 'city_id'),
            'top_properties' => $this->topProperties(),
        ]);
    }

    /**
     * One row per day for the last DAYS days (days without rows are filled with 0).
     *
     * @return list<array{date: string, count: int}>
     */
    private function dailySeries(string $table, CarbonImmutable $since): array
    {
        $counts = DB::table($table)
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $series = [];
        for ($i = 0; $i < self::DAYS; $i++) {
            $day = $since->addDays($i)->toDateString();
            $series[] = ['date' => $day, 'count' => (int) ($counts[$day] ?? 0)];
        }

        return $series;
    }

    /**
     * COUNT(*) per distinct value of $column; every key in $keys is present (0 if absent).
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  list<string>  $keys
     * @return array<string, int>
     */
    private function groupedCounts(Builder $query, string $column, array $keys): array
    {
        $rows = $query
            ->select($column, DB::raw('COUNT(*) as total'))
            ->groupBy($column)
            ->pluck('total', $column);

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = (int) ($rows[$key] ?? 0);
        }

        return $result;
    }

    /** @return array<string, int> */
    private function usersByRole(): array
    {
        $rows = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->select('roles.name', DB::raw('COUNT(*) as total'))
            ->groupBy('roles.name')
            ->pluck('total', 'name');

        $result = [];
        foreach (['client', 'owner', 'admin'] as $role) {
            $result[$role] = (int) ($rows[$role] ?? 0);
        }

        return $result;
    }

    /**
     * Listings per property type / city (top 8). Soft-deleted listings are excluded
     * automatically by the Property model's global scope.
     *
     * @return list<array{name_ar: string, name_en: string, count: int}>
     */
    private function propertiesByLookup(string $table, string $foreignKey): array
    {
        return Property::query()
            ->join($table, "{$table}.id", '=', "properties.{$foreignKey}")
            ->select("{$table}.id", "{$table}.name_ar", "{$table}.name_en", DB::raw('COUNT(properties.id) as total'))
            ->groupBy("{$table}.id", "{$table}.name_ar", "{$table}.name_en")
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'name_ar' => (string) $row->name_ar,
                'name_en' => (string) $row->name_en,
                'count' => (int) $row->total,
            ])
            ->all();
    }

    /** @return list<array<string, mixed>> */
    private function topProperties(): array
    {
        return Property::query()
            ->where('status', 'published')
            ->withCount('inquiries')
            ->orderByDesc('views_count')
            ->limit(5)
            ->get(['id', 'slug', 'title_ar', 'title_en', 'views_count'])
            ->map(fn ($property) => [
                'id' => $property->id,
                'slug' => $property->slug,
                'title_ar' => $property->title_ar,
                'title_en' => $property->title_en,
                'views' => (int) $property->views_count,
                'inquiries' => (int) $property->inquiries_count,
            ])
            ->all();
    }
}
