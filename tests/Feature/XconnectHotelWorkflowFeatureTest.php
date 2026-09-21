<?php

namespace Tests\Feature;

use App\Models\Users\User;
use App\Services\Xconnect\XconnectClient;
use App\Services\Xconnect\XconnectHotelParser;
use App\Services\Xconnect\XconnectHotelService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class XconnectHotelWorkflowFeatureTest extends TestCase
{
    protected function bindReadyXconnect(): void
    {
        Http::fake(function ($request) {
            $url = $request->url();

            if (str_contains($url, 'AvailabilityWithCancellation') || preg_match('#/Availability/?$#', $url)) {
                return Http::response([
                    'Error' => [],
                    'AvailabilityRS' => [
                        'SearchKey' => 'JUNI_test_1',
                        'Count' => 1,
                        'Currency' => 'USD',
                        'HotelResult' => [[
                            'StartPrice' => 100,
                            'HotelId' => 6003,
                            'HotelName' => 'Test Hotel',
                            'HotelOption' => [[
                                'HotelOptionId' => '0|6003||0',
                                'MinPrice' => 100,
                                'IsCombineRoom' => false,
                                'HotelRooms' => [[
                                    [
                                        'RoomNo' => '1',
                                        'RoomTypeName' => 'Deluxe',
                                        'MealName' => 'RO',
                                        'Price' => 100,
                                        'BookingStatus' => 'Available',
                                        'RoomToken' => 'tok1',
                                        'MappedMealName' => 'Room Only',
                                    ],
                                ]],
                            ]],
                        ]],
                    ],
                ], 200);
            }

            if (str_contains($url, 'ReCheck')) {
                return Http::response([
                    'Error' => [],
                    'Currency' => 'USD',
                    'ReCheckRS' => [[
                        'RoomNo' => '1',
                        'Price' => 100,
                        'BookingStatus' => 'Available',
                        'RoomToken' => 'tok1',
                    ]],
                ], 200);
            }

            if (str_contains($url, 'PreBook')) {
                return Http::response([
                    'Error' => [],
                    'PreBookRS' => [
                        'HotelOption' => [
                            'HotelName' => 'Test Hotel',
                            'Nationality' => 'india',
                            'BookingToken' => 'BOOKING-TOKEN',
                            'HotelRooms' => [[
                                'UniqueId' => 11,
                                'RoomNo' => '1',
                                'RoomTypeName' => 'Deluxe',
                                'MealName' => 'RO',
                                'Price' => 100,
                                'MappedMealName' => 'Room Only',
                            ]],
                        ],
                        'Currency' => 'USD',
                    ],
                ], 200);
            }

            if (str_contains($url, '/Book')) {
                return Http::response([
                    'Error' => [],
                    'BookRS' => [
                        'BookingId' => 61405,
                        'ReferenceNo' => 'JUNIMA091901633',
                        'InternalReference' => 'INTREF-'.uniqid('', true),
                        'Currency' => 'USD',
                    ],
                ], 200);
            }

            if (str_contains($url, 'BookingDetail')) {
                return Http::response([
                    'Error' => [],
                    'BookingDetailRS' => [
                        'HotelOption' => [
                            'BookingId' => 61405,
                            'ReferenceNo' => 'JUNIMA091901633',
                            'InternalReference' => 'INTREF1',
                            'HotelName' => 'Test Hotel',
                        ],
                    ],
                ], 200);
            }

            if (str_contains($url, 'CheckHotelCancellationCharges')) {
                return Http::response([
                    'Error' => [],
                    'CheckHotelCancellationChargesRS' => [
                        'HotelOption' => [
                            'BookingId' => 61405,
                            'CancelCode' => 'CANCEL-CODE',
                            'TotalCharge' => 0,
                            'TotalRefund' => 100,
                        ],
                    ],
                ], 200);
            }

            if (str_contains($url, 'CancelBooking')) {
                return Http::response([
                    'Error' => [],
                    'CancelRS' => [
                        'HotelOption' => [
                            'BookingId' => 61405,
                            'HotelRooms' => [[
                                'CancelStatus' => 1,
                                'Message' => 'Room Cancelled Successfully!!!',
                            ]],
                        ],
                    ],
                ], 200);
            }

            if (str_contains($url, 'Countries')) {
                return Http::response([['CountryId' => '1', 'Name' => 'INDIA']], 200);
            }

            return Http::response(['Error' => [['ErrorCode' => 'UNKNOWN', 'ErrorDescription' => $url]]], 404);
        });

        $this->app->bind(XconnectHotelService::class, function () {
            $client = new class extends XconnectClient
            {
                protected function integrationBlockedMessage(): ?string
                {
                    return null;
                }

                protected function isConfigured(): bool
                {
                    return true;
                }

                protected function config(): array
                {
                    return [
                        'token' => 'test-token',
                        'base_url' => 'https://xconnect.test',
                        'timeout' => 30,
                        'default_currency' => 'USD',
                        'default_nationality' => 'india',
                    ];
                }

                public function baseUrl(): string
                {
                    return 'https://xconnect.test';
                }
            };

            return new class($client, new XconnectHotelParser) extends XconnectHotelService
            {
                public function isReady(): bool
                {
                    return true;
                }
            };
        });
    }

    public function test_full_hotel_journey_search_prebook_book_cancel(): void
    {
        $this->bindReadyXconnect();

        $checkIn = now()->addMonths(2)->format('Y-m-d');
        $checkOut = now()->addMonths(2)->addDay()->format('Y-m-d');

        $this->post(route('frontend.hotels.search'), [
            'city_id' => '89006',
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => 2,
            'nationality' => 'india',
        ])->assertRedirect(route('frontend.hotels.results'));

        $solutions = session('public.hotel_search.result.solutions');
        $this->assertIsArray($solutions);
        $this->assertNotEmpty($solutions);
        $key = $solutions[0]['key'];

        $this->post(route('frontend.hotels.prebook'), [
            'solution_key' => $key,
        ])->assertRedirect(route('frontend.hotels.book'));

        $this->post(route('frontend.hotels.book.store'), [
            'prefix' => 'Mr.',
            'first_name' => 'Test',
            'last_name' => 'Traveller',
            'email' => 'test@example.com',
        ])->assertRedirect(route('frontend.hotels.confirmation'));

        $reservationId = session('public.hotel_booking.reservation_id');
        $this->assertNotEmpty($reservationId);

        $this->get(route('frontend.hotels.reservations.show', $reservationId))
            ->assertOk()
            ->assertSee('Test Hotel');

        $this->post(route('frontend.hotels.reservations.cancel', $reservationId), [
            'reason' => 'test',
        ])->assertRedirect();

        $this->assertDatabaseHas('hotel_reservations', [
            'id' => $reservationId,
            'status' => 'cancelled',
            'reference_no' => 'JUNIMA091901633',
        ]);
    }

    public function test_admin_can_open_xconnect_integration(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->first();
        if (! $user) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $this->actingAs($user)
            ->get(route('admin.integrations.edit', ['slug' => 'xconnect']))
            ->assertOk()
            ->assertSee('Xconnect Hotel API', false);
    }
}
