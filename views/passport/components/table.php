<div class="table-responsive table-scroll">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll"></th>
                <th>ID</th>
                <th>Name</th>
                <th>Passport</th>
                <th>Country</th>
                <th>Documents</th>
                <th>Application</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($applicants as $applicant): ?>
            <tr
                data-documents="<?= htmlspecialchars((string) $applicant['documentsNormalized'], ENT_QUOTES, 'UTF-8') ?>"
                data-application="<?= htmlspecialchars((string) $applicant['applicationNormalized'], ENT_QUOTES, 'UTF-8') ?>"
                data-date="<?= htmlspecialchars((string) $applicant['submissionDateIso'], ENT_QUOTES, 'UTF-8') ?>"
                data-country="<?= htmlspecialchars((string) $applicant['country'], ENT_QUOTES, 'UTF-8') ?>"
                data-passport-number="<?= htmlspecialchars((string) ($applicant['passport']['number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                >
                <td><input type="checkbox" class="row-checkbox"></td>
                <td>#<?= str_pad((string) $applicant['id'], 3, '0', STR_PAD_LEFT) ?></td>
                <td><strong><?= htmlspecialchars((string) $applicant['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                <td><span class="status-dot status-<?= htmlspecialchars((string) $applicant['passport']['status'], ENT_QUOTES, 'UTF-8') ?>"></span> <?= htmlspecialchars((string) $applicant['passport']['number'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) $applicant['country'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge rounded-pill <?= htmlspecialchars(document_status_badge_class($applicant['documents']['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(document_status_label($applicant['documents']['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><span class="badge rounded-pill <?= htmlspecialchars(application_status_badge_class($applicant['application']['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(application_status_label($applicant['application']['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                <td>
                    <button class="btn btn-sm btn-primary js-applicant-action" data-action="view" data-id="<?= (int) $applicant['id'] ?>" data-name="<?= htmlspecialchars((string) $applicant['name'], ENT_QUOTES, 'UTF-8') ?>"><i class="fa fa-eye"></i></button>
                    <button class="btn btn-sm btn-success js-applicant-action" data-action="upload-document" data-id="<?= (int) $applicant['id'] ?>"><i class="fa fa-upload"></i></button>
                    <button class="btn btn-sm btn-info js-applicant-action" data-action="update-applicant" data-id="<?= (int) $applicant['id'] ?>" data-name="<?= htmlspecialchars((string) $applicant['name'], ENT_QUOTES, 'UTF-8') ?>"><i class="fa fa-edit"></i></button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if ($applicants === []): ?>
            <tr><td colspan="8" class="text-center py-4 text-muted">No applicants found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
