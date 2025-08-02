<?php

namespace App\Module\CraftMigration;

class CraftMigrationConstants
{
    // Field IDs
    public const int FIELD_RESOURCE_FILE = 29;
    public const int FIELD_SERIES_ENTRIES = 457;
    public const int FIELD_CONTENT_BLOCKS = 6;
    public const int FIELD_FEATURE_IMAGE = 15; // Related content field

    // Group IDs
    public const int TAG_GROUP_RESOURCES = 2;
    public const int CATEGORY_GROUP_TOPICS = 3;
    public const int CATEGORY_GROUP_ROLES = 1;
    public const int CATEGORY_GROUP_TRADES = 12;

    // Matrix Block Type IDs
    public const array MATRIX_BLOCK_TYPES = [1, 19, 18, 27, 23, 36, 24, 26, 29, 21, 32, 37];

    // Entry Type IDs
    public const int ENTRY_TYPE_EXCLUDE = 18;

    // Matrix Field IDs for relations
    public const int MATRIX_FIELD_RESOURCE_FILE = 342;

    // Batch Processing Defaults
    public const int DEFAULT_BATCH_SIZE = 100;
    public const int DEFAULT_MEMORY_LIMIT_MB = 512;
    public const int PROGRESS_LOG_INTERVAL = 50;
}
