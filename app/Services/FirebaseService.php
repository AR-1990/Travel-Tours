<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Messaging;

class FirebaseService
{
    protected $database;
    protected $auth;
    protected $messaging;

    public function __construct()
    {
        try {
            $credentialsPath = config('firebase.credentials.file');
            $databaseUrl = config('firebase.database.url');

            if (empty($credentialsPath)) {
                throw new \Exception('Firebase credentials path is not configured. Please set FIREBASE_CREDENTIALS in .env');
            }

            // If path contains placeholder text or is empty, use default location
            if (str_contains($credentialsPath, '/full/absolute/path/to/')) {
                $credentialsPath = storage_path('app/firebase-credentials.json');
            }

            if (!file_exists($credentialsPath)) {
                throw new \Exception("Firebase credentials file not found at: {$credentialsPath}. Please check your FIREBASE_CREDENTIALS setting in .env");
            }

            if (empty($databaseUrl)) {
                throw new \Exception('Firebase database URL is not configured. Please set FIREBASE_DATABASE_URL in .env');
            }

            $factory = (new Factory)
                ->withServiceAccount($credentialsPath)
                ->withDatabaseUri($databaseUrl);

            $this->database = $factory->createDatabase();
            $this->auth = $factory->createAuth();
            $this->messaging = $factory->createMessaging();

            \Log::info('Firebase service initialized successfully');
        } catch (\Exception $e) {
            \Log::error('FirebaseService initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send message to Firebase Realtime Database
     */
    public function sendMessage($conversationId, $message)
    {
        $reference = $this->database->getReference('messages/' . $conversationId);

        // ✅ Generate full photo URL
        $photoUrl = null;
        if (!empty($message->sender->photo)) {
            $photoUrl = getImageUrl($message->sender->photo);
        }

        $messageData = [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'content' => $message->content,
            'created_at' => $message->created_at->toIso8601String(),
            'read_at' => $message->read_at?->toIso8601String(),
            'edited_at' => $message->edited_at?->toIso8601String(), // ✅ Add this
            'deleted_at' => $message->deleted_at?->toIso8601String(), // ✅ Add this
            'is_deleted' => !is_null($message->deleted_at), // ✅ Add this
            'sender' => [
                'id' => $message->sender->id,
                'first_name' => $message->sender->first_name,
                'last_name' => $message->sender->last_name,
                'email' => $message->sender->email,
                'photo' => $photoUrl,
                'slug' => $message->sender->slug,
                'user_has_photo' => !empty($message->sender->photo),
                'user_initials' => strtoupper(
                    substr($message->sender->first_name, 0, 1) .
                        substr($message->sender->last_name, 0, 1)
                ),
            ],
            'reactions' => [],
        ];

        // ✅ Increment unread count for receiver
        $this->updateUnreadCount($message->receiver_id, $conversationId, true);

        return $reference->getChild((string)$message->id)->set($messageData);
    }

    /**
     * Update message in Firebase
     */
    public function updateMessage(int $conversationId, int $messageId, array $data): void
    {
        try {
            $reference = $this->database->getReference("messages/{$conversationId}/{$messageId}");
            $reference->update($data);

            \Log::info('Message updated in Firebase', [
                'conversation_id' => $conversationId,
                'message_id' => $messageId,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update message in Firebase: ' . $e->getMessage(), [
                'conversation_id' => $conversationId,
                'message_id' => $messageId
            ]);
            throw $e;
        }
    }

    /**
     * Delete message from Firebase
     */
    public function deleteMessage(int $conversationId, int $messageId): void
    {
        try {
            $reference = $this->database->getReference("messages/{$conversationId}/{$messageId}");
            $reference->remove();

            \Log::info('Message deleted from Firebase', [
                'conversation_id' => $conversationId,
                'message_id' => $messageId
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to delete message from Firebase: ' . $e->getMessage(), [
                'conversation_id' => $conversationId,
                'message_id' => $messageId
            ]);
            throw $e;
        }
    }

    /**
     * Add reaction to message in Firebase
     */
    public function addReaction($conversationId, $messageId, $reaction)
    {
        $reference = $this->database->getReference(
            "messages/{$conversationId}/{$messageId}/reactions/{$reaction->id}"
        );

        return $reference->set([
            'id' => $reaction->id,
            'user_id' => $reaction->user_id,
            'emoji' => $reaction->emoji,
            'user' => [
                'id' => $reaction->user->id,
                'first_name' => $reaction->user->first_name,
                'last_name' => $reaction->user->last_name,
            ],
        ]);
    }

    /**
     * Remove reaction from Firebase
     */
    public function removeReaction($conversationId, $messageId, $reactionId)
    {
        $reference = $this->database->getReference(
            "messages/{$conversationId}/{$messageId}/reactions/{$reactionId}"
        );

        return $reference->remove();
    }

    /**
     * Update typing status in Firebase
     */
    public function updateTypingStatus($conversationId, $userId, $user, $isTyping = true)
    {
        $reference = $this->database->getReference('typing/' . $conversationId . '/' . $userId);

        if ($isTyping) {
            return $reference->set([
                'user_id' => $userId,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'timestamp' => time(),
            ]);
        } else {
            return $reference->remove();
        }
    }

    /**
     * Update user online status
     */
    public function updateUserOnlineStatus($userId, $isOnline = true)
    {
        $reference = $this->database->getReference('presence/users/' . $userId);

        if ($isOnline) {
            $reference->set([
                'online' => true,
                'last_active' => time(),
            ]);

            $reference->onDisconnect()->set([
                'online' => false,
                'last_active' => time(),
            ]);
        } else {
            $reference->set([
                'online' => false,
                'last_active' => time(),
            ]);
        }
    }

    /**
     * Create custom token for Firebase Authentication
     */
    public function createCustomToken($userId)
    {
        try {
            $token = $this->auth->createCustomToken((string) $userId);

            // ✅ Convert token object to string
            $tokenString = $token->toString();

            \Log::info('Firebase token created', [
                'user_id' => $userId,
                'token_length' => strlen($tokenString),
            ]);

            return $tokenString;
        } catch (\Exception $e) {
            \Log::error('Firebase custom token creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if user is online via Firebase
     */
    public function isUserOnline($userId)
    {
        try {
            $userIdString = (string) $userId;
            $snapshot = $this->database->getReference("presence/users/{$userIdString}")->getSnapshot();

            if (!$snapshot->exists()) {
                return false;
            }

            $presence = $snapshot->getValue();
            $isOnline = $presence['online'] ?? false;
            $lastActive = $presence['last_active'] ?? 0;

            // Consider user online if they were active in the last 5 minutes
            $currentTime = time();
            $timeDiff = $currentTime - $lastActive;
            $isRecentlyActive = $timeDiff < 300; // 5 minutes

            return $isOnline && $isRecentlyActive;
        } catch (\Exception $e) {
            \Log::error('Error checking online status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ NEW: Update unread count for a user's conversation
     */
    public function updateUnreadCount($userId, $conversationId, $increment = true)
    {
        try {
            $userIdString = (string) $userId;
            $conversationIdString = (string) $conversationId;
            $unreadRef = $this->database->getReference("unread_counts/{$userIdString}/{$conversationIdString}");

            if ($increment) {
                $currentCount = $unreadRef->getValue() ?? 0;
                $newCount = $currentCount + 1;
                $unreadRef->set($newCount);
            } else {
                // Clear unread count
                $unreadRef->set(0);
            }

            // Update total unread count
            $this->updateTotalUnreadCount($userId);

            \Log::info('Updated unread count', [
                'user_id' => $userId,
                'conversation_id' => $conversationId,
                'increment' => $increment
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update unread count: ' . $e->getMessage(), [
                'user_id' => $userId,
                'conversation_id' => $conversationId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * ✅ NEW: Update total unread count for a user
     */
    public function updateTotalUnreadCount($userId)
    {
        try {
            $userIdString = (string) $userId;
            $unreadRef = $this->database->getReference("unread_counts/{$userIdString}");
            $allUnread = $unreadRef->getValue() ?? [];

            $total = 0;
            if (is_array($allUnread)) {
                $total = array_sum(array_values($allUnread));
            }

            $totalRef = $this->database->getReference("unread_totals/{$userIdString}");
            $totalRef->set($total);

            \Log::info('Updated total unread count', [
                'user_id' => $userId,
                'total' => $total
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update total unread count: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * ✅ NEW: Clear unread count for a conversation
     */
    public function clearUnreadCount($userId, $conversationId)
    {
        $this->updateUnreadCount($userId, $conversationId, false);
    }

    /**
     * Send FCM push notification to a device token
     */
    public function sendFCMNotification($fcmToken, $title, $body, $data = [])
    {
        try {
            $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(\Kreait\Firebase\Messaging\Notification::create($title, $body))
                ->withData($data);

            $result = $this->messaging->send($message);

            \Log::info('FCM notification sent successfully', [
                'fcm_token' => substr($fcmToken, 0, 20) . '...',
                'title' => $title,
                'message_id' => $result
            ]);

            return $result;
        } catch (\Kreait\Firebase\Exception\Messaging\InvalidArgument $e) {
            \Log::error('FCM notification failed - Invalid argument', [
                'error' => $e->getMessage(),
                'fcm_token' => substr($fcmToken, 0, 20) . '...'
            ]);
            return false;
        } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
            // Token is invalid or expired, remove it
            \Log::warning('FCM token not found or invalid', [
                'fcm_token' => substr($fcmToken, 0, 20) . '...'
            ]);
            return false;
        } catch (\Exception $e) {
            \Log::error('FCM notification failed', [
                'error' => $e->getMessage(),
                'fcm_token' => substr($fcmToken, 0, 20) . '...',
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send FCM notification to multiple tokens
     */
    public function sendFCMNotificationToMultiple($fcmTokens, $title, $body, $data = [])
    {
        if (empty($fcmTokens)) {
            return [];
        }

        $results = [];
        foreach ($fcmTokens as $token) {
            $result = $this->sendFCMNotification($token, $title, $body, $data);
            $results[$token] = $result;
        }

        return $results;
    }
}
