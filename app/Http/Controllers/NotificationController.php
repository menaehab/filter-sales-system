<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        // Log activity in trait-style format
        activity()
            ->performedOn(auth()->user())
            ->causedBy(auth()->user())
            ->event('activity_read_notification')
            ->withProperties([
                'notification_id' => $notification->id,
                'notification_type' => data_get($notification->data, 'type'),
                'notification_message' => data_get($notification->data, 'message'),
                'read_at' => $notification->read_at,
            ])
            ->log(__('keywords.activity_read_notification'));

        return response()->json([
            'success' => true,
            'message' => __('keywords.notification_marked_read_success'),
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $unreadCount = auth()->user()->unreadNotifications->count();

        auth()->user()->unreadNotifications->markAsRead();

        // Log activity in trait-style format
        activity()
            ->performedOn(auth()->user())
            ->causedBy(auth()->user())
            ->event('activity_read_all_notifications')
            ->withProperties([
                'marked_count' => $unreadCount,
            ])
            ->log(__('keywords.activity_read_all_notifications'));

        return response()->json([
            'success' => true,
            'message' => __('keywords.notification_marked_read_count_success', ['count' => $unreadCount]),
        ]);
    }

    /**
     * Delete notification
     */
    public function delete($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        // Log activity in trait-style format
        activity()
            ->performedOn(auth()->user())
            ->causedBy(auth()->user())
            ->event('activity_delete_notification')
            ->withProperties([
                'notification_id' => $notification->id,
                'notification_type' => data_get($notification->data, 'type'),
                'notification_message' => data_get($notification->data, 'message'),
                'was_read' => $notification->read_at !== null,
            ])
            ->log(__('keywords.activity_delete_notification'));

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => __('keywords.notification_deleted_success'),
        ]);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        $notifications = auth()->user()->readNotifications;
        $deletedCount = $notifications->count();

        // Log activity in trait-style format
        activity()
            ->performedOn(auth()->user())
            ->causedBy(auth()->user())
            ->event('activity_delete_all_read_notifications')
            ->withProperties([
                'deleted_count' => $deletedCount,
            ])
            ->log(__('keywords.activity_delete_all_read_notifications'));

        $notifications->each->delete();

        return response()->json([
            'success' => true,
            'message' => __('keywords.notification_deleted_count_success', ['count' => $deletedCount]),
        ]);
    }

    /**
     * Delete all notifications
     */
    public function deleteAll()
    {
        $notifications = auth()->user()->notifications;
        $deletedCount = $notifications->count();

        // Log activity in trait-style format
        activity()
            ->performedOn(auth()->user())
            ->causedBy(auth()->user())
            ->event('activity_delete_all_notifications')
            ->withProperties([
                'deleted_count' => $deletedCount,
            ])
            ->log(__('keywords.activity_delete_all_notifications'));

        $notifications->each->delete();

        return response()->json([
            'success' => true,
            'message' => __('keywords.notification_deleted_count_success', ['count' => $deletedCount]),
        ]);
    }
}
