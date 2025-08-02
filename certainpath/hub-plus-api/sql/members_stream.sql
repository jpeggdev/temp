CREATE TABLE IF NOT EXISTS members_stream
(
    id                            INTEGER GENERATED ALWAYS AS IDENTITY,
    created_at                    TIMESTAMP WITH TIME ZONE DEFAULT now(),
    tenant                        TEXT,
    trade                         TEXT,
    software                      TEXT,
    active_member                 TEXT,
    membership_type               TEXT,
    current_status                TEXT,
    customer_id                   TEXT,
    customer_first_name           TEXT,
    customer_last_name            TEXT,
    customer_name                 TEXT,
    customer_phone_numbers        TEXT,
    customer_phone_number_primary TEXT,
    street                        TEXT,
    unit                          TEXT,
    city                          TEXT,
    state                         TEXT,
    zip                           TEXT,
    country                       TEXT,
    processed                     BOOLEAN DEFAULT FALSE,
    version                       TEXT,
    hub_plus_import_id            INT
    );

-- alter table members_stream
--     owner to unification_ingest_admin;
