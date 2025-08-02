create table if not exists members_stream
(
    id                            integer generated always as identity,
    created_at                    timestamp with time zone default now(), -- new
    tenant                        text,
    trade                         text, -- new
    software                      text, -- new
    active_member                 text,
    membership_type               text,
    current_status                text,
    customer_id                   text,
    customer_first_name           text,
    customer_last_name            text,
    customer_name                 text,
    customer_phone_numbers        text,
    customer_phone_number_primary text,
    street                        text,
    unit                          text,
    city                          text,
    state                         text,
    zip                           text,
    country                       text,
    processed                     boolean default false,
    version                       text,
    hub_plus_import_id            int
);

-- alter table members_stream
--     owner to unification_ingest_admin;