namespace HeyDav.Application.AgentManagement.Workspace;

public interface ISharedWorkspaceManager
{
    // Workspace Management
    Task<Guid> CreateWorkspaceAsync(WorkspaceDefinition definition, CancellationToken cancellationToken = default);
    Task<bool> DeleteWorkspaceAsync(Guid workspaceId, CancellationToken cancellationToken = default);
    Task<SharedWorkspace?> GetWorkspaceAsync(Guid workspaceId, CancellationToken cancellationToken = default);
    Task<IEnumerable<SharedWorkspace>> GetWorkspacesAsync(Guid? ownerId = null, CancellationToken cancellationToken = default);
    Task<bool> UpdateWorkspaceAsync(Guid workspaceId, WorkspaceUpdate update, CancellationToken cancellationToken = default);

    // Access Control
    Task<bool> AddMemberAsync(Guid workspaceId, Guid agentId, WorkspaceRole role, CancellationToken cancellationToken = default);
    Task<bool> RemoveMemberAsync(Guid workspaceId, Guid agentId, CancellationToken cancellationToken = default);
    Task<bool> UpdateMemberRoleAsync(Guid workspaceId, Guid agentId, WorkspaceRole newRole, CancellationToken cancellationToken = default);
    Task<IEnumerable<WorkspaceMember>> GetMembersAsync(Guid workspaceId, CancellationToken cancellationToken = default);
    Task<bool> HasAccessAsync(Guid workspaceId, Guid agentId, WorkspacePermission requiredPermission, CancellationToken cancellationToken = default);

    // Resource Management
    Task<Guid> AddResourceAsync(Guid workspaceId, WorkspaceResource resource, CancellationToken cancellationToken = default);
    Task<bool> RemoveResourceAsync(Guid workspaceId, Guid resourceId, CancellationToken cancellationToken = default);
    Task<WorkspaceResource?> GetResourceAsync(Guid workspaceId, Guid resourceId, CancellationToken cancellationToken = default);
    Task<IEnumerable<WorkspaceResource>> GetResourcesAsync(Guid workspaceId, ResourceType? type = null, CancellationToken cancellationToken = default);
    Task<bool> UpdateResourceAsync(Guid workspaceId, Guid resourceId, ResourceUpdate update, CancellationToken cancellationToken = default);

    // Document Management
    Task<Guid> CreateDocumentAsync(Guid workspaceId, WorkspaceDocument document, CancellationToken cancellationToken = default);
    Task<bool> UpdateDocumentAsync(Guid workspaceId, Guid documentId, string content, Guid editorId, CancellationToken cancellationToken = default);
    Task<WorkspaceDocument?> GetDocumentAsync(Guid workspaceId, Guid documentId, CancellationToken cancellationToken = default);
    Task<IEnumerable<WorkspaceDocument>> GetDocumentsAsync(Guid workspaceId, CancellationToken cancellationToken = default);
    Task<bool> DeleteDocumentAsync(Guid workspaceId, Guid documentId, CancellationToken cancellationToken = default);
    Task<IEnumerable<DocumentVersion>> GetDocumentHistoryAsync(Guid workspaceId, Guid documentId, CancellationToken cancellationToken = default);

    // Collaboration Features
    Task<bool> LockResourceAsync(Guid workspaceId, Guid resourceId, Guid agentId, TimeSpan? lockDuration = null, CancellationToken cancellationToken = default);
    Task<bool> UnlockResourceAsync(Guid workspaceId, Guid resourceId, Guid agentId, CancellationToken cancellationToken = default);
    Task<ResourceLock?> GetResourceLockAsync(Guid workspaceId, Guid resourceId, CancellationToken cancellationToken = default);
    Task<IEnumerable<ResourceLock>> GetActiveLocksAsync(Guid workspaceId, CancellationToken cancellationToken = default);

    // Activity Tracking
    Task<bool> LogActivityAsync(Guid workspaceId, WorkspaceActivity activity, CancellationToken cancellationToken = default);
    Task<IEnumerable<WorkspaceActivity>> GetActivityLogAsync(Guid workspaceId, TimeSpan? period = null, CancellationToken cancellationToken = default);
    Task<IEnumerable<WorkspaceActivity>> GetAgentActivityAsync(Guid workspaceId, Guid agentId, TimeSpan? period = null, CancellationToken cancellationToken = default);

    // Search and Discovery
    Task<IEnumerable<WorkspaceSearchResult>> SearchWorkspacesAsync(string query, Guid? requestingAgentId = null, CancellationToken cancellationToken = default);
    Task<IEnumerable<ResourceSearchResult>> SearchResourcesAsync(Guid workspaceId, string query, ResourceType? type = null, CancellationToken cancellationToken = default);
    Task<IEnumerable<WorkspaceDocument>> SearchDocumentsAsync(Guid workspaceId, string query, CancellationToken cancellationToken = default);

    // Synchronization
    Task<WorkspaceSync> GetSyncStatusAsync(Guid workspaceId, CancellationToken cancellationToken = default);
    Task<bool> SyncWorkspaceAsync(Guid workspaceId, CancellationToken cancellationToken = default);
    Task<IEnumerable<SyncConflict>> GetSyncConflictsAsync(Guid workspaceId, CancellationToken cancellationToken = default);
    Task<bool> ResolveSyncConflictAsync(Guid workspaceId, Guid conflictId, ConflictResolution resolution, CancellationToken cancellationToken = default);

    // Analytics and Insights
    Task<WorkspaceAnalytics> GetWorkspaceAnalyticsAsync(Guid workspaceId, TimeSpan? period = null, CancellationToken cancellationToken = default);
    Task<IEnumerable<CollaborationInsight>> GetCollaborationInsightsAsync(Guid workspaceId, CancellationToken cancellationToken = default);
}

public record WorkspaceDefinition(
    string Name,
    string Description,
    WorkspaceType Type,
    Guid OwnerId,
    WorkspaceVisibility Visibility = WorkspaceVisibility.Private,
    Dictionary<string, object>? Settings = null,
    IEnumerable<string>? Tags = null);

public record SharedWorkspace(
    Guid Id,
    string Name,
    string Description,
    WorkspaceType Type,
    Guid OwnerId,
    WorkspaceVisibility Visibility,
    WorkspaceStatus Status,
    IEnumerable<WorkspaceMember> Members,
    IEnumerable<WorkspaceResource> Resources,
    Dictionary<string, object> Settings,
    IEnumerable<string> Tags,
    DateTime CreatedAt,
    DateTime LastModifiedAt,
    DateTime LastAccessedAt);

public record WorkspaceUpdate(
    string? Name = null,
    string? Description = null,
    WorkspaceVisibility? Visibility = null,
    WorkspaceStatus? Status = null,
    Dictionary<string, object>? Settings = null,
    IEnumerable<string>? Tags = null);

public record WorkspaceMember(
    Guid AgentId,
    string AgentName,
    WorkspaceRole Role,
    DateTime JoinedAt,
    DateTime LastActiveAt,
    IEnumerable<WorkspacePermission> Permissions);

public record WorkspaceResource(
    Guid Id,
    string Name,
    string Description,
    ResourceType Type,
    string Location,
    long Size,
    string ContentType,
    Guid CreatedById,
    DateTime CreatedAt,
    DateTime LastModifiedAt,
    Dictionary<string, object>? Metadata = null,
    IEnumerable<string>? Tags = null);

public record ResourceUpdate(
    string? Name = null,
    string? Description = null,
    string? Location = null,
    Dictionary<string, object>? Metadata = null,
    IEnumerable<string>? Tags = null);

public record WorkspaceDocument(
    Guid Id,
    string Title,
    string Content,
    DocumentType Type,
    Guid AuthorId,
    DateTime CreatedAt,
    DateTime LastModifiedAt,
    Guid LastModifiedById,
    int Version,
    DocumentStatus Status,
    IEnumerable<string>? Tags = null);

public record DocumentVersion(
    int Version,
    string Content,
    Guid ModifiedById,
    DateTime ModifiedAt,
    string? ChangeDescription = null);

public record ResourceLock(
    Guid ResourceId,
    Guid LockedById,
    DateTime LockedAt,
    DateTime? ExpiresAt,
    string? Reason = null);

public record WorkspaceActivity(
    Guid Id,
    Guid WorkspaceId,
    Guid AgentId,
    ActivityType Type,
    string Description,
    Dictionary<string, object>? Data = null,
    DateTime Timestamp = default);

public record WorkspaceSearchResult(
    Guid WorkspaceId,
    string Name,
    string Description,
    WorkspaceType Type,
    double RelevanceScore,
    IEnumerable<string> MatchedTerms);

public record ResourceSearchResult(
    Guid ResourceId,
    string Name,
    string Description,
    ResourceType Type,
    double RelevanceScore,
    IEnumerable<string> MatchedTerms,
    string? PreviewContent = null);

public record WorkspaceSync(
    Guid WorkspaceId,
    DateTime LastSyncAt,
    SyncStatus Status,
    int PendingChanges,
    IEnumerable<Guid> ConflictingResources);

public record SyncConflict(
    Guid Id,
    Guid ResourceId,
    string ResourceName,
    ConflictType Type,
    IEnumerable<ConflictVersion> Versions,
    DateTime DetectedAt);

public record ConflictVersion(
    Guid ModifiedById,
    DateTime ModifiedAt,
    string Content,
    string? Description = null);

public record ConflictResolution(
    Guid ConflictId,
    ResolutionType Type,
    string? MergedContent = null,
    Guid? SelectedVersionAgentId = null);

public record WorkspaceAnalytics(
    Guid WorkspaceId,
    TimeSpan AnalysisPeriod,
    int TotalMembers,
    int ActiveMembers,
    int TotalResources,
    int ModifiedResources,
    int TotalActivities,
    Dictionary<ActivityType, int> ActivityBreakdown,
    Dictionary<Guid, int> MemberActivityCounts,
    Dictionary<ResourceType, int> ResourceTypeDistribution,
    IEnumerable<PopularResource> MostAccessedResources,
    double CollaborationScore);

public record PopularResource(
    Guid ResourceId,
    string Name,
    ResourceType Type,
    int AccessCount,
    int ModificationCount);

public record CollaborationInsight(
    string Type,
    string Description,
    double Score,
    IEnumerable<Guid> InvolvedAgents,
    Dictionary<string, object>? Data = null);

public enum WorkspaceType
{
    Project,
    Research,
    Documentation,
    Collaboration,
    Training,
    Experiment,
    Knowledge,
    Template
}

public enum WorkspaceVisibility
{
    Private,
    TeamVisible,
    OrganizationVisible,
    Public
}

public enum WorkspaceStatus
{
    Active,
    Archived,
    Suspended,
    ReadOnly
}

public enum WorkspaceRole
{
    Owner,
    Admin,
    Editor,
    Contributor,
    Viewer
}

public enum WorkspacePermission
{
    Read,
    Write,
    Delete,
    Share,
    AdministerMembers,
    AdministerSettings,
    Lock,
    Export
}

public enum ResourceType
{
    Document,
    Data,
    Code,
    Image,
    Video,
    Audio,
    Archive,
    Link,
    Note,
    Template,
    Config,
    Log
}

public enum DocumentType
{
    Text,
    Markdown,
    Code,
    Json,
    Xml,
    Csv,
    Spreadsheet,
    Presentation,
    Diagram,
    Other
}

public enum DocumentStatus
{
    Draft,
    Review,
    Approved,
    Published,
    Archived
}

public enum ActivityType
{
    Created,
    Modified,
    Deleted,
    Accessed,
    Shared,
    Locked,
    Unlocked,
    Commented,
    Tagged,
    Moved,
    Copied,
    Exported,
    Imported
}

public enum SyncStatus
{
    Synchronized,
    Synchronizing,
    OutOfSync,
    ConflictDetected,
    Failed
}

public enum ConflictType
{
    ContentConflict,
    MetadataConflict,
    PermissionConflict,
    VersionConflict
}

public enum ResolutionType
{
    AcceptLocal,
    AcceptRemote,
    Merge,
    Manual
}