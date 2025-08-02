#!/bin/bash

# Check if the field name and file name are provided
if [[ -z "$1" || -z "$2" ]]; then
  echo "Usage: $0 <field_name> <csv_file>"
  exit 1
fi

FIELD_NAME="$1"
CSV_FILE="$2"

# Check if the file exists
if [[ ! -f "$CSV_FILE" ]]; then
  echo "File $CSV_FILE not found!"
  exit 1
fi

# Extract the specified field from the CSV file and print only non-zero values
csvcut -c "$FIELD_NAME" "$CSV_FILE" | awk 'NR>1 && $0 != "" {print $0}'