<div class="widget">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="h5 mb-0">Communication Threads</h2>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger">Unread: <?= (int) ($unreadCount ?? 0) ?></span>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="markAllMessagesReadBtn">
                Mark All as Read
            </button>
        </div>
    </div>

    <form method="GET" action="messages.php" class="d-flex flex-wrap gap-2 mb-3">
        <select name="filter" class="form-select" style="max-width: 220px;">
            <?php
                $filters = [
                    'all' => 'All',
                    'customers' => 'Customers',
                    'internal' => 'Internal',
                    'unread' => 'Unread'
                ];
            ?>
            <?php foreach ($filters as $key => $label): ?>
            <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= ($selectedFilter ?? 'all') === $key ? 'selected' : '' ?>>
                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Apply Filter</button>
    </form>

    <form id="sendMessageForm" class="border rounded p-3 mb-3 bg-light">
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label small mb-1">Recipient Type</label>
                <select name="target_type" class="form-select form-select-sm">
                    <option value="customer">Customer</option>
                    <option value="internal">Internal Staff</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Receiver ID</label>
                <input type="number" min="1" name="receiver_id" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Module Origin</label>
                <input type="text" name="module_origin" class="form-control form-control-sm" value="CRM" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Message</label>
                <input type="text" name="message_text" class="form-control form-control-sm" placeholder="Type your message..." required>
            </div>
        </div>
        <div class="mt-2 d-flex justify-content-end">
            <button type="submit" class="btn btn-sm btn-primary">Send Message</button>
        </div>
    </form>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Participant</th>
                    <th>Module</th>
                    <th>Last Message</th>
                    <th>Unread</th>
                    <th>Last Activity</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($threads)): ?>
                    <?php foreach ($threads as $thread): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars(strtoupper((string) ($thread['participant_role'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong>
                            #<?= (int) ($thread['participant_id'] ?? 0) ?>
                        </td>
                        <td><?= htmlspecialchars((string) ($thread['module_origin'] ?? 'General'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($thread['last_message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <span class="badge <?= ((int) ($thread['unread_count'] ?? 0)) > 0 ? 'bg-danger' : 'bg-secondary' ?>">
                                <?= (int) ($thread['unread_count'] ?? 0) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars((string) ($thread['last_message_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No messages found for this filter.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sendMessageForm = document.getElementById('sendMessageForm');
    if (!sendMessageForm) {
        return;
    }

    sendMessageForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        const formData = new FormData(sendMessageForm);
        const payload = {
            sender_role: 'ae',
            target_type: formData.get('target_type') || 'customer',
            receiver_id: Number(formData.get('receiver_id') || 0),
            module_origin: String(formData.get('module_origin') || 'CRM'),
            message_text: String(formData.get('message_text') || '')
        };

        try {
            const url = new URL(window.location.href);
            url.search = '';
            url.searchParams.set('ajax', 'send-message');

            const response = await fetch(url.toString(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (!response.ok || !result.ok) {
                throw new Error(result.message || 'Unable to send message.');
            }

            window.location.reload();
        } catch (error) {
            showError(error.message || 'Unable to send message.');
        }
    });
});
</script>
