<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;

class PartnerInquiry extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'partner_type',
        'name',
        'email',
        'phone',
        'company',
        'message',
        'status',
        'ip_address',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    /**
     * @return array<string, array{number: int, title: string, text: string, icon: string, tone: string, features: list<string>}>
     */
    public static function partnerTypes(): array
    {
        return [
            'b2b' => [
                'number' => 1,
                'title' => 'B2B Partner',
                'text' => 'For travel agencies & businesses who want to sell travel services.',
                'icon' => 'fas fa-user-friends',
                'tone' => 'blue',
                'features' => [
                    'Agent Portal Access',
                    'Competitive Rates',
                    'Credit Facility',
                    'Dedicated Support',
                ],
            ],
            'b2c' => [
                'number' => 2,
                'title' => 'B2C Partner',
                'text' => 'For businesses who want to sell travel services directly to customers.',
                'icon' => 'fas fa-user',
                'tone' => 'green',
                'features' => [
                    'Retail Booking System',
                    'Best Customer Prices',
                    'Multiple Payment Options',
                    'Marketing Support',
                ],
            ],
            'api' => [
                'number' => 3,
                'title' => 'API Partner',
                'text' => 'Integrate our powerful travel API into your platform or system.',
                'icon' => 'fas fa-code',
                'tone' => 'purple',
                'features' => [
                    'Real-time Inventory',
                    'Seamless Integration',
                    'Global Content',
                    'Technical Support',
                ],
            ],
            'whitelabel' => [
                'number' => 4,
                'title' => 'Whitelable Partner',
                'text' => 'Launch your own travel brand with our white-label solution.',
                'icon' => 'fas fa-desktop',
                'tone' => 'orange',
                'features' => [
                    'Your Own Brand',
                    'Custom Domain',
                    'Full System Control',
                    'End-to-End Support',
                ],
            ],
            'supplier' => [
                'number' => 5,
                'title' => 'Become Supplier',
                'text' => 'For hotels, airlines, transfer services & other suppliers to connect with us.',
                'icon' => 'fas fa-briefcase',
                'tone' => 'teal',
                'features' => [
                    'Global Visibility',
                    'Increase Bookings',
                    'Secure Payments',
                    'Long Term Partnership',
                ],
            ],
        ];
    }

    public static function typeKeys(): array
    {
        return array_keys(self::partnerTypes());
    }

    public function typeTitle(): string
    {
        return (string) (self::partnerTypes()[$this->partner_type]['title'] ?? ucfirst((string) $this->partner_type));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_CONTACTED => 'Contacted',
            self::STATUS_CLOSED => 'Closed',
            default => 'New',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_CONTACTED => 'bg-info text-dark',
            self::STATUS_CLOSED => 'bg-secondary',
            default => 'bg-success',
        };
    }
}
