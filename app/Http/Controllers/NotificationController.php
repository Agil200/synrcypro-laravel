<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Throwable;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        // Pembuatan notifikasi ditangani scheduler. Endpoint ini hanya membaca
        // data agar membuka dropdown tidak membuat notifikasi berulang.
        $data = $this->visibleNotificationsQuery()
            ->latest()
            ->limit(100)
            ->get()
            ->unique(
                fn (Notification $notification): string =>
                    $this->notificationKey($notification)
            )
            ->take(20)
            ->values();

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'unread_count' => $this->unreadCountValue(),
            'data' => $data,
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'count' => $this->unreadCountValue(),
        ]);
    }

    public function read(int $id): JsonResponse
    {
        $notification = $this->visibleNotificationsQuery()->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        if ($notification->type === 'birthday') {
            $this->markBirthdayDuplicatesAsRead($notification);
        } else {
            $notification->update(['is_read' => true]);
        }

        return response()->json([
            'success' => true,
            'count' => $this->unreadCountValue(),
        ]);
    }

    private function visibleNotificationsQuery(): Builder
    {
        $role = trim((string) (auth()->user()?->role ?? ''));
        $today = Carbon::now('Asia/Jakarta');
        $birthdayStart = $today->copy()->startOfDay()->utc();
        $birthdayEnd = $today->copy()->endOfDay()->utc();

        return Notification::query()
            ->where(function (Builder $query) use ($role): void {
                $query->where('target_role', 'all');

                if ($role !== '') {
                    $query->orWhere('target_role', $role);
                }
            })
            ->where(function (Builder $query) use (
                $birthdayStart,
                $birthdayEnd
            ): void {
                // Notifikasi selain ulang tahun tetap mengikuti riwayat.
                // Notifikasi ulang tahun hanya berlaku pada hari tersebut.
                $query->where('type', '!=', 'birthday')
                    ->orWhere(function (Builder $birthdayQuery) use (
                        $birthdayStart,
                        $birthdayEnd
                    ): void {
                        $birthdayQuery
                            ->where('type', 'birthday')
                            ->whereBetween(
                                'notification_date',
                                [$birthdayStart, $birthdayEnd]
                            );
                    });
            });
    }

    private function unreadCountValue(): int
    {
        return $this->visibleNotificationsQuery()
            ->where('is_read', false)
            ->get()
            ->unique(
                fn (Notification $notification): string =>
                    $this->notificationKey($notification)
            )
            ->count();
    }

    private function notificationKey(Notification $notification): string
    {
        if ($notification->type !== 'birthday') {
            return 'notification:'.$notification->getKey();
        }

        return 'birthday:'.$this->localNotificationDate($notification);
    }

    private function localNotificationDate(
        Notification $notification
    ): string {
        try {
            return Carbon::parse($notification->notification_date)
                ->setTimezone('Asia/Jakarta')
                ->toDateString();
        } catch (Throwable) {
            return (string) $notification->notification_date;
        }
    }

    private function markBirthdayDuplicatesAsRead(
        Notification $notification
    ): void {
        try {
            $date = Carbon::parse($notification->notification_date)
                ->setTimezone('Asia/Jakarta');

            $start = $date->copy()->startOfDay()->utc();
            $end = $date->copy()->endOfDay()->utc();

            $this->visibleNotificationsQuery()
                ->where('type', 'birthday')
                ->whereBetween('notification_date', [$start, $end])
                ->update(['is_read' => true]);
        } catch (Throwable) {
            $notification->update(['is_read' => true]);
        }
    }
}