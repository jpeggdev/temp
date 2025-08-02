INSERT INTO invoices_stream (
    created_at, tenant, trade, software, invoice_number, invoice_summary,
    job_number, job_type, customer_id, customer_first_name, customer_last_name,
    customer_name, customer_phone_numbers, customer_phone_number_primary,
    zone, street, unit, city, state, zip, country, total, first_appointment,
    summary, processed, hub_plus_import_id
) VALUES
      (
          NOW(), 'UNI1', 'hvac', 'ServiceTitan',
          '113353493', null, '113353493', 'job-type',
          '4595546', 'Micah', 'Marsh', 'Micah Marsh', '(303) 909-4530', NULL,
          'zone', '4670 Osceola Street', NULL, 'Denver', 'CO', '80212',
          'USA', 3076.95, '2022-05-27T08:00:00',
          '05/27 - Pipe is needing replace and leaking in a crawl space.' || E'\n' ||
    'Arrival window given: 8am - 5pm' || E'\n' ||
    'Pricing: $49 dispatch for non-members' || E'\n' ||
    'Water/Gas line: Water Line or Pipe' || E'\n' ||
    'Location of break:  in the pipe  leading up to wash room and dishwasher he says.' || E'\n' ||
    'Age of home: 1955' || E'\n' ||
    'Notes: Customer doesn''t feel ok running water in sink and wash room - maybe interested in a whole pipe replacement.',
          false, 101
      ),
      (
          NOW(), 'UNI1', 'hvac', 'ServiceTitan',
          '114297893', 'sample-invoice-summary', '114297893', 'sample-job-type',
          '4557152', 'Russ & Diane', 'Kyncl', 'Russ & Diane Kyncl', '(303) 503-9770', NULL,
          'sample-zone', '3372 Vivian Drive', NULL, 'Wheat Ridge', 'CO', '80033',
          'USA', 655, '2022-06-02T08:00:00',
          '06/02 Our issue is a outside hose spigot valve, I think. No problem when closed, when open no water comes out of spigot, but runs into the wall and basement' || E'\n' ||
    'Arrival window given: 8a-5p' || E'\n' ||
    'Pricing: $49 dispatch for non-members' || E'\n' ||
    'Location of faucet:' || E'\n' ||
    'Age of home: 1973' || E'\n' ||
    'Notes:',
          false, 101
      ),
      (
          NOW(), 'UNI1', 'hvac', 'ServiceTitan',
          '114580468', 'sample-invoice-summary', '114580468', 'sample-job-type',
          '84641649', 'Valerie', 'Kummer', 'Valerie Kummer', '(701) 570-1201', NULL,
          'sample-zone', '11473 West Asbury Court', NULL, 'Lakewood', 'CO', '80228',
          'USA', 0, '2022-06-03T08:00:00',
          'COORDINATOR ONLY' || E'\n' || E'\n' ||
    'Install Goodman 80% 80K 2-stg and 80% 100L 2-stg with 2 16Seer 3 Ton AC''s',
          false, 101
      );