<?php

declare(strict_types=1);

namespace App\Audit;

/**
 * Canonical audit event names.
 *
 * Every event is defined in docs/modules/master-data/13-Audit.md §6–§12 — no
 * event is invented here. Code references these constants so audit events stay
 * stable and testable.
 */
final class AuditEvent
{
    // Master data (§6)
    public const CREATED = 'created';
    public const UPDATED = 'updated';
    public const DEACTIVATED = 'deactivated';
    public const ARCHIVED = 'archived';
    public const PURGED = 'purged';

    // Duplicate (§7)
    public const DUPLICATE_CANDIDATE_CREATED = 'candidate_created';
    public const DUPLICATE_REVIEWED = 'reviewed';
    public const DUPLICATE_THRESHOLD_CHANGED = 'threshold_changed';

    // Merge / unmerge (§8)
    public const MERGE_INITIATED = 'merge.initiated';
    public const MERGE_APPROVED = 'merge.approved';
    public const MERGE_REJECTED = 'merge.rejected';
    public const MERGE_EXECUTED = 'merge.executed';
    public const UNMERGE_EXECUTED = 'unmerge.executed';

    // Golden record (§9)
    public const GOLDEN_ESTABLISHED = 'golden.established';
    public const GOLDEN_UPDATED = 'golden.updated';
    public const GOLDEN_LINK_CHANGED = 'golden.link_changed';

    // Approval (§10)
    public const APPROVAL_DECIDED = 'approval.decided';
    public const APPROVAL_MFA = 'approval.mfa';

    // Import / export (§11)
    public const IMPORT_APPLIED = 'import.applied';
    public const IMPORT_ROLLBACK = 'import.rollback';
    public const EXPORT_RUN = 'export.run';

    // Integration (§12)
    public const INTEGRATION_CHANGED = 'integration.changed';
    public const CROSS_REFERENCE_CHANGED = 'cross_reference.changed';
}
