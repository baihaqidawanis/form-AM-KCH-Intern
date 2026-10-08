BEGIN;

CREATE TABLE IF NOT EXISTS rtwt_master_part_sync_outbox (
    id BIGSERIAL PRIMARY KEY,
    source_reference VARCHAR(255) NOT NULL UNIQUE,
    payload JSONB NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'PENDING' CHECK (status IN ('PENDING', 'RETRY', 'SYNCED')),
    attempts INTEGER NOT NULL DEFAULT 0 CHECK (attempts >= 0),
    next_attempt_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    last_error TEXT NULL,
    last_response JSONB NULL,
    synced_at TIMESTAMPTZ NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS rtwt_master_part_sync_outbox_pending_idx
    ON rtwt_master_part_sync_outbox (next_attempt_at, id)
    WHERE status IN ('PENDING', 'RETRY');

COMMIT;
