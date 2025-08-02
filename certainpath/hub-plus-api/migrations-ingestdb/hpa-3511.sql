-- Add new columns
ALTER TABLE IF EXISTS invoices_stream
ADD COLUMN IF NOT EXISTS invoice_summary text,
ADD COLUMN IF NOT EXISTS job_type text,
ADD COLUMN IF NOT EXISTS zone text;