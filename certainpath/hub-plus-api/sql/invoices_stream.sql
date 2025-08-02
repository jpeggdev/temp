create table if not exists invoices_stream
(
    id                            integer generated always as identity,
    created_at                    timestamp with time zone default now(), -- new
    tenant                        text,
    trade                         text, -- new
    software                      text, -- new
    invoice_number                text,
    invoice_summary               text,
    job_number                    text,
    job_type                      text,
    customer_id                   text,
    customer_first_name           text,
    customer_last_name            text,
    customer_name                 text,
    customer_phone_numbers        text,
    customer_phone_number_primary text,
    zone                          text,
    street                        text,
    unit                          text,
    city                          text,
    state                         text,
    zip                           text,
    country                       text,
    total                         double precision,
    first_appointment             text,
    summary                       text,
    processed                     boolean default false,
    hub_plus_import_id            INT
);

-- alter table invoices_stream
--     owner to unification_ingest_admin;
