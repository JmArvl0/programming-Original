<div class="booked-guests-section">
    <h4 class="panel-subtitle" id="bookedGuestsTitle">Booked Guests</h4>
    <div class="booked-guest-list" id="bookedGuestList">
        <?php if (!empty($selectedDateBookings)): ?>
            <?php foreach ($selectedDateBookings as $booking): ?>
                <?php
                $tourId = (string) ($booking['tour_id'] ?? '');
                $guestName = (string) ($booking['guest_name'] ?? 'Unknown Guest');
                $bookingStatus = (string) ($booking['booking_status'] ?? 'Confirmed');
                $seatSlot = isset($booking['seat_slot']) && $booking['seat_slot'] !== '' ? (string) $booking['seat_slot'] : 'N/A';
                ?>
            <div class="booked-guest-item" data-tour-id="<?= htmlspecialchars($tourId) ?>">
                <div class="booked-guest-name"><?= htmlspecialchars($guestName) ?></div>
                <div class="booked-guest-meta">
                    <span><?= htmlspecialchars($bookingStatus) ?></span>
                    <span>Seat/Slot: <?= htmlspecialchars($seatSlot) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
        <div class="no-guests-message" id="noBookingsMessage">No booked guests for this date yet.</div>
        <?php endif; ?>
    </div>
</div>
