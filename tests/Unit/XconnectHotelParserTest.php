<?php

namespace Tests\Unit;

use App\Services\Xconnect\XconnectHotelParser;
use PHPUnit\Framework\TestCase;

class XconnectHotelParserTest extends TestCase
{
    public function test_parse_availability_flattens_nested_rooms(): void
    {
        $parser = new XconnectHotelParser;
        $solutions = $parser->parseAvailability([
            'AvailabilityRS' => [
                'SearchKey' => 'SK1',
                'Currency' => 'USD',
                'HotelResult' => [[
                    'HotelId' => 6003,
                    'StartPrice' => 89.88,
                    'HotelOption' => [[
                        'HotelOptionId' => '0|6003||0',
                        'MinPrice' => 89.88,
                        'IsCombineRoom' => false,
                        'HotelRooms' => [[
                            [
                                'RoomNo' => '1',
                                'RoomTypeName' => 'Deluxe Room',
                                'Price' => 44.94,
                                'RoomToken' => 'a',
                            ],
                            [
                                'RoomNo' => '2',
                                'RoomTypeName' => 'Deluxe Room',
                                'Price' => 44.94,
                                'RoomToken' => 'b',
                            ],
                        ]],
                    ]],
                ]],
            ],
        ], ['check_in' => '2026-10-15', 'check_out' => '2026-10-16']);

        $this->assertCount(1, $solutions);
        $this->assertSame('xconnect', $solutions[0]['provider']);
        $this->assertSame(89.88, $solutions[0]['total_price']);
        $this->assertCount(2, $solutions[0]['rooms']);
        $this->assertSame('a', $solutions[0]['room_tokens'][0]['RoomToken']);
    }

    public function test_parse_prebook_sums_room_prices(): void
    {
        $parser = new XconnectHotelParser;
        $pre = $parser->parsePreBook([
            'PreBookRS' => [
                'Currency' => 'USD',
                'HotelOption' => [
                    'HotelName' => 'Furama',
                    'BookingToken' => 'tok',
                    'HotelRooms' => [
                        ['UniqueId' => 1, 'RoomNo' => '1', 'Price' => 40],
                        ['UniqueId' => 2, 'RoomNo' => '2', 'Price' => 50],
                    ],
                ],
            ],
        ]);

        $this->assertSame('tok', $pre['booking_token']);
        $this->assertSame(90.0, $pre['total_price']);
    }
}
