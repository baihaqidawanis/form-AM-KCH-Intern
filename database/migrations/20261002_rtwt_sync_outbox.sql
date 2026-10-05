BEGIN;

CREATE TABLE IF NOT EXISTS rtwt_sync_outbox (
    id BIGSERIAL PRIMARY KEY,
    source_reference VARCHAR(255) NOT NULL,
    operation VARCHAR(20) NOT NULL DEFAULT 'SYNC' CHECK (operation IN ('SYNC', 'CANCEL')),
    payload JSONB NOT NULL DEFAULT '{}'::jsonb,
    photo_path TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING' CHECK (status IN ('PENDING', 'RETRY', 'REVIEW', 'SYNCED')),
    attempts INTEGER NOT NULL DEFAULT 0,
    next_attempt_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    last_error TEXT NULL,
    last_response JSONB NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    synced_at TIMESTAMPTZ NULL,
    UNIQUE (source_reference, operation)
);

CREATE INDEX IF NOT EXISTS rtwt_sync_outbox_pending_idx
    ON rtwt_sync_outbox (next_attempt_at, id)
    WHERE status IN ('PENDING', 'RETRY', 'REVIEW');

COMMIT;
