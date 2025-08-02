<?php

namespace App\Module\CraftMigration\SQL;

class CraftMigrationQueries
{
    public const string RESOURCES = <<<'SQL'
        SELECT
            e.fieldLayoutId,
            et.name as type,
            es.slug,
            es.uri,
            en.postDate,
            e.enabled,
            c.*
        FROM
            elements e
                INNER JOIN
            elements_sites es ON e.id = es.elementId
                INNER JOIN
            entries en on en.id = e.id
                INNER JOIN
            entrytypes et ON en.typeId = et.id
                INNER JOIN
            content c ON e.id = c.elementId
        WHERE
            e.canonicalId IS NULL
          AND e.dateDeleted IS NULL
          AND e.draftId IS NULL
          AND e.revisionId IS NULL
          AND es.uri LIKE "%resources/%"
          AND et.id <> :excludeEntryType
        ORDER BY
            e.id
        SQL;

    public const string ELEMENT = <<<'SQL'
        SELECT
            *
        FROM
            elements e
        WHERE
            e.id = :elementId;
        SQL;

    public const string ELEMENT_FIELDS = <<<'SQL'
        select
            f.id,
            e.id as elementId,
            f.handle as handle,
            f.name,
            flf.sortOrder
        from
            elements e
        inner join
            fieldlayoutfields flf on e.fieldLayoutId = flf.layoutId
        inner join
            fields f on flf.fieldId = f.id
        where
            e.id = :elementId
        and
            f.id in (:fieldContentBlocks, :fieldResourceFile, :fieldSeriesEntries)
        order by
            flf.sortOrder;
        SQL;

    public const string ELEMENT_TAGS = <<<'SQL'
        SELECT
            t.id,
            co.title as name
        FROM
            tags t
        INNER JOIN
            relations r ON r.targetId = t.id
        INNER JOIN
            elements e ON e.id = r.sourceId
        INNER JOIN
            content co ON co.elementId = r.targetId
        WHERE
            t.groupId = :groupId
        AND
            e.id = :elementId
        SQL;

    public const string ELEMENT_RESOURCE_FILE = <<<'SQL'
        SELECT
            a.filename,
            vf.path,
            v.settings
        FROM
            relations r
        INNER JOIN
            assets a ON r.targetId = a.id
        INNER JOIN
            volumefolders vf ON vf.id = a.folderId
        INNER JOIN
            volumes v ON v.id = a.volumeId
        WHERE
            r.sourceId = :elementId
          AND
            r.fieldId = :fieldResourceFile
        SQL;

    public const string ELEMENT_SERIES_ENTRIES = <<<'SQL'
        SELECT
            es.slug, c.title
        FROM
            relations r
        INNER JOIN
            elements_sites es on r.targetId = es.elementId
        INNER JOIN
            content c ON c.elementId = es.elementId
        WHERE
            r.sourceId = :elementId
        AND
            r.fieldId = :fieldSeriesEntries
        SQL;

    public const string CATEGORIES = <<<'SQL'
        SELECT
            DISTINCT
                c.id,
                co.title as name
        FROM
            categories c
                INNER JOIN
            relations r ON r.targetId = c.id
                INNER JOIN
            elements e ON e.id = r.sourceId
                INNER JOIN
            content co ON co.elementId = r.targetId
        WHERE
            c.groupId = :categoryGroupTopics
        SQL;

    public const string TAGS = <<<'SQL'
        SELECT
            DISTINCT
            t.id,
            co.title as name
        FROM
            tags t
                INNER JOIN
            relations r ON r.targetId = t.id
                INNER JOIN
            elements e ON e.id = r.sourceId
                INNER JOIN
            content co ON co.elementId = r.targetId
        WHERE
            t.groupId = :tagGroupResources
        SQL;

    public const string ELEMENT_CATEGORIES = <<<'SQL'
        SELECT
            c2.id,
            c.title as name
        FROM
            relations r
                INNER JOIN
            categories c2 ON c2.id = r.targetId
                INNER JOIN
            content c ON r.targetId = c.elementId
        WHERE
            r.sourceId = :elementId
            AND c2.groupId = :groupId;
        SQL;

    public const string ELEMENT_RELATED_CONTENT = <<<'SQL'
        SELECT
            r.targetId as elementId
        FROM
            relations r
        WHERE
            r.sourceId = :elementId
            AND r.fieldId = :fieldFeatureImage
        SQL;

    public const string MATRIX_BLOCKS_CONTENT_BY_OWNER = <<<'SQL'
        SELECT
            m.id as id,
            m.ownerId as entryId,
            m.sortOrder,
            m.fieldId as fieldId,
            mbt.name as typeName,
            mbt.handle as typeHandle
        FROM
            matrixblocks m
                INNER JOIN
            matrixblocktypes mbt ON m.typeId = mbt.id
                INNER JOIN
            elements e ON m.id = e.id
                INNER JOIN
            matrixcontent_contentblocks mc ON m.id = mc.elementId
        WHERE
            m.typeId IN (:matrixBlockTypes)
          AND e.dateDeleted IS NULL
          AND e.enabled = 1
          AND m.ownerid = :elementId
        ORDER BY
            m.ownerId, m.fieldId, m.sortOrder
        SQL;

    public const string ASSET = <<<'SQL'
        SELECT
            a.filename,
            vf.path,
            v.settings
        FROM
            assets a
        inner join
            volumes v on v.id = a.volumeId
        inner join
            volumefolders vf on vf.id = a.folderId
        WHERE
            a.id = :id;
        SQL;

    public const string FEATURE_IMAGE = <<<'SQL'
        select
            a.filename,
            vf.path,
            v.settings
        from
            assets a
        inner join
            relations r on a.id = r.targetId
        inner join
            fields f on f.id = r.fieldId
        inner join
            volumes v on v.id = a.volumeId
        inner join
            volumefolders vf on vf.id = a.folderId
        where
            r.sourceId = :elementId and f.handle = 'featureImage';
        SQL;

    public const string FIELD = <<<'SQL'
        SELECT
            f.*
        FROM
            fields f
        WHERE
            f.id = :fieldId;
        SQL;

    public const string CONTENT_BLOCK_ASSETS = <<<'SQL'
        SELECT
            a.filename,
            v.settings,
            vf.path
        from
            relations r
        INNER JOIN
            assets a on a.id = r.targetId
        INNER JOIN
            volumefolders vf ON a.folderId = vf.id
        INNER JOIN
            volumes v ON a.volumeId = v.id
        where
            r.sourceId = :elementId;
        SQL;

    public const string MATRIX_BLOCK_RICH_TEXT = <<<'SQL'
    SELECT
        m.id as id,
        m.ownerId as entryId,
        m.typeId,
        m.sortOrder,
        m.fieldId,
        mbt.name as typeName,
        mbt.handle as typeHandle,
        'text' as resourceType,
        mc.field_richText_richText as content
    FROM
        matrixblocks m
        INNER JOIN matrixblocktypes mbt ON m.typeId = mbt.id
        INNER JOIN matrixcontent_contentblocks mc ON m.id = mc.elementId
        INNER JOIN elements e ON m.id = e.id
    WHERE
        mbt.handle = 'richText'
        AND m.id = :elementId
        AND e.dateDeleted IS NULL
        AND e.enabled = 1
    ORDER BY
        m.sortOrder;
    SQL;

    public const string MATRIX_BLOCK_HEADING = <<<'SQL'
    SELECT
        m.id as id,
        m.ownerId as entryId,
        m.typeId,
        m.sortOrder,
        m.fieldId,
        mbt.name as typeName,
        mbt.handle as typeHandle,
        'text' as resourceType,
        mc.field_heading_heading,
        mc.field_heading_style
    FROM
        matrixblocks m
        INNER JOIN matrixblocktypes mbt ON m.typeId = mbt.id
        INNER JOIN matrixcontent_contentblocks mc ON m.id = mc.elementId
        INNER JOIN elements e ON m.id = e.id
    WHERE
        mbt.handle = 'heading'
        AND m.id = :elementId
        AND e.dateDeleted IS NULL
        AND e.enabled = 1
    ORDER BY
        m.sortOrder;
    SQL;

    public const string MATRIX_BLOCK_QUOTE = <<<'SQL'
    SELECT
        m.id as id,
        m.ownerId as entryId,
        m.typeId,
        m.sortOrder,
        m.fieldId,
        mbt.name as typeName,
        mbt.handle as typeHandle,
        'text' as resourceType,
        mc.field_quote_quote as content,
        mc.field_quote_attribution as attribution
    FROM
        matrixblocks m
        INNER JOIN matrixblocktypes mbt ON m.typeId = mbt.id
        INNER JOIN matrixcontent_contentblocks mc ON m.id = mc.elementId
        INNER JOIN elements e ON m.id = e.id
    WHERE
        mbt.handle = 'quote'
        AND m.id = :elementId
        AND e.dateDeleted IS NULL
        AND e.enabled = 1
    ORDER BY
        m.sortOrder;
    SQL;

    public const string MATRIX_BLOCK_IMAGE = <<<'SQL'
    SELECT
        m.id as id,
        m.ownerId as entryId,
        m.typeId,
        r.sortOrder,
        r.fieldId,
         a.filename as filename,
        vf.path as path,
        v.settings as settings,
        mbt.name as typeName,
        mbt.handle as typeHandle,
        'image' as resourceType,
        CONCAT(JSON_UNQUOTE(JSON_EXTRACT(v.settings, '$.customSubfolder')), '/', vf.path, a.filename) as content,
        co.title as alt_text
    FROM
        matrixblocks m
        INNER JOIN matrixblocktypes mbt ON m.typeId = mbt.id
        INNER JOIN matrixcontent_contentblocks mc ON m.id = mc.elementId
        INNER JOIN elements e ON m.id = e.id
        LEFT JOIN relations r ON r.sourceId = m.id
        LEFT JOIN assets a ON a.id = r.targetId
        LEFT JOIN volumefolders vf ON a.folderId = vf.id
        LEFT JOIN volumes v ON a.volumeId = v.id
        LEFT JOIN content co ON co.elementId = a.id
    WHERE
        mbt.handle = 'image'
        AND m.id = :elementId
        AND e.dateDeleted IS NULL
        AND e.enabled = 1
    ORDER BY
        m.sortOrder, r.sortOrder;
    SQL;

    public const string MATRIX_BLOCK_EMBED = <<<'SQL'
    SELECT
        m.id as id,
        m.ownerId as entryId,
        m.typeId,
        m.sortOrder,
        m.fieldId,
        mbt.name as typeName,
        mbt.handle as typeHandle,
        'link' as resourceType,
        mc.field_embed_embed as content
    FROM
        matrixblocks m
        INNER JOIN matrixblocktypes mbt ON m.typeId = mbt.id
        INNER JOIN matrixcontent_contentblocks mc ON m.id = mc.elementId
        INNER JOIN elements e ON m.id = e.id
    WHERE
        mbt.handle = 'embed'
        AND m.id = :elementId
        AND e.dateDeleted IS NULL
        AND e.enabled = 1
    ORDER BY
        m.sortOrder;
    SQL;

    public const string MATRIX_BLOCK_RAW_HTML = <<<'SQL'
    SELECT
        m.id as id,
        m.ownerId as entryId,
        m.typeId,
        m.sortOrder,
        m.fieldId,
        mbt.name as typeName,
        mbt.handle as typeHandle,
        'text' as resourceType,
        mc.field_rawHtml_html as content,
        mc.field_rawHtml_css as css_content,
        mc.field_rawHtml_js as js_content
    FROM
        matrixblocks m
        INNER JOIN matrixblocktypes mbt ON m.typeId = mbt.id
        INNER JOIN matrixcontent_contentblocks mc ON m.id = mc.elementId
        INNER JOIN elements e ON m.id = e.id
    WHERE
        mbt.handle = 'rawHtml'
        AND m.id = :elementId
        AND e.dateDeleted IS NULL
        AND e.enabled = 1
    ORDER BY
        m.sortOrder;
    SQL;

    public const string MATRIX_BLOCK_COLUMN_CONTENT = <<<'SQL'
    SELECT
        m.id as id,
        m.ownerId as entryId,
        m.typeId,
        m.sortOrder,
        m.fieldId,
        mbt.name as typeName,
        mbt.handle as typeHandle,
        'text' as resourceType,
        mc.field_columnContent_leftColumn as left_column,
        mc.field_columnContent_rightColumn as right_column
    FROM
        matrixblocks m
        INNER JOIN matrixblocktypes mbt ON m.typeId = mbt.id
        INNER JOIN matrixcontent_contentblocks mc ON m.id = mc.elementId
        INNER JOIN elements e ON m.id = e.id
    WHERE
        mbt.handle = 'columnContent'
        AND m.id = :elementId
        AND e.dateDeleted IS NULL
        AND e.enabled = 1
    ORDER BY
        m.sortOrder;
    SQL;

    public const string MATRIX_BLOCK_RESOURCE_COURSE = <<<'SQL'
    SELECT
        m.id as id,
        m.ownerId as entryId,
        m.typeId,
        m.sortOrder,
        m.fieldId,
        mbt.name as typeName,
        mbt.handle as typeHandle,
        'link' as resourceType,
        mc.field_resourceCourse_name_ as title,
        mc.field_resourceCourse_shortDescription as shortDescription,
        mc.field_resourceCourse_courseUrl as content
    FROM
        matrixblocks m
        INNER JOIN matrixblocktypes mbt ON m.typeId = mbt.id
        INNER JOIN matrixcontent_contentblocks mc ON m.id = mc.elementId
        INNER JOIN elements e ON m.id = e.id
    WHERE
        mbt.handle = 'resourceCourse'
        AND m.id = :elementId
        AND e.dateDeleted IS NULL
        AND e.enabled = 1
    ORDER BY
        m.sortOrder;
    SQL;

    public const string MATRIX_BLOCK_RESOURCE_VIDEO = <<<'SQL'
    SELECT
        m.id as id,
        m.ownerId as entryId,
        m.typeId,
        m.sortOrder,
        m.fieldId,
        mbt.name as typeName,
        mbt.handle as typeHandle,
        'vimeo' as resourceType,
        mc.field_resourceVideo_name_ as video_name,
        mc.field_resourceVideo_shortDescription as shortDescription,
        mc.field_resourceVideo_video as content
    FROM
        matrixblocks m
        INNER JOIN matrixblocktypes mbt ON m.typeId = mbt.id
        INNER JOIN matrixcontent_contentblocks mc ON m.id = mc.elementId
        INNER JOIN elements e ON m.id = e.id
    WHERE
        mbt.handle = 'resourceVideo'
        AND m.id = :elementId
        AND e.dateDeleted IS NULL
        AND e.enabled = 1
    ORDER BY
        m.sortOrder;
    SQL;

    public const string MATRIX_BLOCK_RESOURCE_YOUTUBE = <<<'SQL'
    SELECT
        m.id as id,
        m.ownerId as entryId,
        m.typeId,
        m.sortOrder,
        m.fieldId,
        mc.field_resourceYoutubeVideo_name_ as title,
        mc.field_resourceYoutubeVideo_shortDescription as shortDescription,
        mc.field_resourceYoutubeVideo_videoUrl as content,
        mbt.name as typeName,
        mbt.handle as typeHandle,
        'youtube' as resourceType
    FROM
        matrixblocks m
        INNER JOIN matrixblocktypes mbt ON m.typeId = mbt.id
        INNER JOIN matrixcontent_contentblocks mc ON m.id = mc.elementId
        INNER JOIN elements e ON m.id = e.id
    WHERE
        mbt.handle = 'resourceYoutubeVideo'
        AND m.id = :elementId
        AND e.dateDeleted IS NULL
        AND e.enabled = 1
    ORDER BY
        m.sortOrder;
    SQL;

    public const string MATRIX_BLOCK_RESOURCE_FILE = <<<'SQL'
    SELECT
        m.id as id,
        m.ownerId as entryId,
        m.typeId,
        r.sortOrder,
        r.fieldId,
        mc.field_resourceFile_shortDescription as shortDescription,
        a.filename as filename,
        vf.path as path,
        v.settings as settings,
        mbt.name as typeName,
        mbt.handle as typeHandle,
        'file' as resourceType,
        CONCAT(JSON_UNQUOTE(JSON_EXTRACT(v.settings, '$.customSubfolder')), '/', vf.path, a.filename) as content,
        c.title
    FROM
        matrixblocks m
        INNER JOIN matrixblocktypes mbt ON m.typeId = mbt.id
        INNER JOIN matrixcontent_contentblocks mc ON m.id = mc.elementId
        INNER JOIN elements e ON m.id = e.id
        LEFT JOIN relations r ON r.sourceId = m.id
        LEFT JOIN assets a ON a.id = r.targetId
        INNER JOIN content c on c.elementId = a.id
        LEFT JOIN volumefolders vf ON a.folderId = vf.id
        LEFT JOIN volumes v ON a.volumeId = v.id
    WHERE
        mbt.handle = 'resourceFile'
        AND m.id = :elementId
        AND e.dateDeleted IS NULL
        AND e.enabled = 1
        AND r.fieldId = :matrixFieldResourceFile
    ORDER BY
        m.sortOrder, r.sortOrder;
    SQL;

    public const string MATRIX_BLOCK_RESOURCE_PAGE = <<<'SQL'
        SELECT
            m.id as id,
            m.ownerId as entryId,
            m.typeId,
            m.sortOrder,
            r.fieldId,
            mc.field_resourcePage_shortDescription as shortDescription,
            c_related.title as title,
            es_related.uri,
            es_related.slug as content,
            mbt.name as typeName,
            mbt.handle as typeHandle,
            'link' as resourceType
        FROM
            matrixblocks m
            INNER JOIN matrixblocktypes mbt ON m.typeId = mbt.id
            INNER JOIN matrixcontent_contentblocks mc ON m.id = mc.elementId
            INNER JOIN elements e ON m.id = e.id
            LEFT JOIN relations r ON r.sourceId = m.id
            LEFT JOIN elements e_related ON e_related.id = r.targetId
            LEFT JOIN content c_related ON c_related.elementId = e_related.id
            LEFT JOIN elements_sites es_related ON es_related.elementId = e_related.id
        WHERE
            mbt.handle = 'resourcePage'
            AND m.id = :elementId
            AND e.dateDeleted IS NULL
            AND e.enabled = 1
            AND (e_related.type = 'craft\\elements\\Entry' OR e_related.id IS NULL)
        ORDER BY
            m.sortOrder, r.sortOrder;
    SQL;

    public const string MATRIX_BLOCK_ENTRY_CARD = <<<'SQL'
        SELECT
            m.id as id,
            m.ownerId as entryId,
            m.typeId,
            r.sortOrder as sortOrder,
            r.fieldId,
            c_child.title as shortDescription,
            es_child.uri as content,
            en_child.postDate as postDate,
            et_child.name as typeName,
            et_child.handle as typeHandle,
            'link' as resourceType,
            c_child.title as title
        FROM
            matrixblocks m
            INNER JOIN matrixblocktypes mbt ON m.typeId = mbt.id
            INNER JOIN elements e ON m.id = e.id
            LEFT JOIN relations r ON r.sourceId = m.id
            LEFT JOIN elements e_child ON r.targetId = e_child.id
            LEFT JOIN entries en_child ON en_child.id = e_child.id
            LEFT JOIN entrytypes et_child ON en_child.typeId = et_child.id
            LEFT JOIN content c_child ON c_child.elementId = e_child.id
            LEFT JOIN elements_sites es_child ON es_child.elementId = e_child.id
        WHERE
            mbt.handle = 'entryCards'
            AND m.id = :elementId
            AND e.dateDeleted IS NULL
            AND e.enabled = 1
            AND (e_child.type = 'craft\\elements\\Entry' OR e_child.id IS NULL)
            AND (e_child.dateDeleted IS NULL OR e_child.id IS NULL)
            AND (e_child.draftId IS NULL OR e_child.id IS NULL)
            AND (e_child.revisionId IS NULL OR e_child.id IS NULL)
        ORDER BY
            m.sortOrder, r.sortOrder;
    SQL;

    public const string MATRIX_BLOCK_RESOURCE_SERIES = <<<'SQL'
        SELECT
            m.id as id,
            m.ownerId as entryId,
            m.typeId,
            m.sortOrder,
            m.fieldId,
            mbt.name as typeName,
            mbt.handle as typeHandle,
            'link' as resourceType,
            mc.field_resourceSeries_shortDescription as shortDescription,
            es.slug as content,
            es.uri,
            c.title as title
        FROM
            matrixblocks m
                INNER JOIN matrixblocktypes mbt ON m.typeId = mbt.id
                INNER JOIN matrixcontent_contentblocks mc ON m.id = mc.elementId
                INNER JOIN relations r ON r.sourceId = m.id
                INNER JOIN elements e ON e.id = r.targetId
                INNER JOIN elements_sites es ON e.id = es.elementId
                INNER JOIN content c ON c.elementId = e.id
        WHERE
            mbt.handle = 'resourceSeries'
          AND m.id = :elementId
          AND e.dateDeleted IS NULL
          AND e.enabled = 1
        ORDER BY
            m.sortOrder;
        SQL;
}
