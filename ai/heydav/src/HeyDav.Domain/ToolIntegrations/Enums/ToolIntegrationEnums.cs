namespace HeyDav.Domain.ToolIntegrations.Enums;

public enum ToolType
{
    ProjectManagement,
    Communication,
    VersionControl,
    CloudStorage,
    Calendar,
    Email,
    VideoConferencing,
    TaskManagement,
    Documentation,
    Analytics,
    CRM,
    DevOps,
    Design,
    Finance,
    Other
}

public enum ConnectionStatus
{
    Disconnected,
    Connecting,
    Connected,
    Failed,
    Disabled,
    Expired
}

public enum AuthenticationMethod
{
    None,
    ApiKey,
    OAuth2,
    BasicAuth,
    BearerToken,
    Custom
}

public enum CapabilityType
{
    Read,
    Write,
    Delete,
    Create,
    Update,
    Search,
    Subscribe,
    Webhook,
    FileUpload,
    FileDownload,
    Notification,
    Analytics,
    Custom
}

public enum WebhookStatus
{
    Active,
    Paused,
    Failed,
    Disabled
}

public enum WebhookEventStatus
{
    Pending,
    Processing,
    Processed,
    Failed,
    Retrying,
    Ignored
}

public enum SyncDirection
{
    HeyDavToExternal,
    ExternalToHeyDav,
    Bidirectional
}

public enum SyncFrequency
{
    Realtime,
    EveryMinute,
    Every5Minutes,
    Every15Minutes,
    Every30Minutes,
    Hourly,
    Every6Hours,
    Daily,
    Weekly,
    Manual
}

public enum ConflictResolutionStrategy
{
    LocalWins,
    RemoteWins,
    MostRecent,
    Manual,
    Merge,
    Skip
}

public enum SyncExecutionStatus
{
    Running,
    Completed,
    Failed,
    Cancelled
}

public enum SyncLogLevel
{
    Debug,
    Info,
    Warning,
    Error
}

public enum RateLimitScope
{
    Global,
    PerConnection,
    PerCapability,
    PerUser
}

public enum WorkflowTriggerType
{
    WebhookEvent,
    DataChange,
    Schedule,
    Manual,
    Condition
}

public enum DataTransformationType
{
    Map,
    Convert,
    Filter,
    Aggregate,
    Custom
}