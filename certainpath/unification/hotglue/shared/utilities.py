import os
import re

def create_directories(*directories):
    for directory in directories:
        os.makedirs(directory, exist_ok=True)

def to_snake_case(string):
    # Replace any non-alphanumeric characters (except underscores) with underscores
    string = string.replace('#', 'num')
    string = string.replace(')', '')
    string = string.replace('(', '')
    string = re.sub(r'[^a-zA-Z0-9_]', '_', string)
    # Replace multiple consecutive underscores with a single underscore
    string = re.sub(r'_+', '_', string)
    # Remove leading or trailing underscores
    return string.strip('_').lower()

def rename_columns_to_snake_case(df):
    new_columns = {col: to_snake_case(col) for col in df.columns}
    return df.rename(columns=new_columns)