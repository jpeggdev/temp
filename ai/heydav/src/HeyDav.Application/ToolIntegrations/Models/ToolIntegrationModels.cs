using HeyDav.Domain.ToolIntegrations.Enums;
using HeyDav.Domain.ToolIntegrations.ValueObjects;

namespace HeyDav.Application.ToolIntegrations.Models;

// Request Models
public record CreateToolConnectionRequest(
    string Name,
    string Description,
    ToolType ToolType,
    AuthenticationMethod AuthMethod,
    string BaseUrl,
    string ApiVersion = "v1",
    ToolConfiguration? Configuration = null,
    Dictionary<string, string>? Credentials = null,
    int RequestsPerMinute = 60,
    int RequestsPerHour = 3600,
    int RequestsPerDay = 86400);

public record UpdateToolConnectionRequest(
    string? Name = null,
    string? Description = null,
    string? BaseUrl = null,
    string? ApiVersion = null,
    ToolConfiguration? Configuration = null,
    int? RequestsPerMinute = null,
    int? RequestsPerHour = null,
    int? RequestsPerDay = null);

public record UpdateCredentialsRequest(
    Dictionary<string, string> Credentials,
    List<string>? Scopes = null,
    DateTime? TokenExpiresAt = null);

// Response Models
public record HealthCheckResult(
    Guid ConnectionId,
    string ConnectionName,
    bool IsHealthy,
    ConnectionStatus Status,
    string? ErrorMessage = null,
    TimeSpan ResponseTime = default,
    DateTime CheckedAt = default)
{
    public DateTime CheckedAt { get; init; } = CheckedAt == default ? DateTime.UtcNow : CheckedAt;
}

public record ConnectionMetrics(
    Guid ConnectionId,
    string ConnectionName,
    int TotalRequests,
    int SuccessfulRequests,
    int FailedRequests,
    double SuccessRate,
    TimeSpan AverageResponseTime,
    DateTime? LastRequest,
    DateTime? LastSuccessfulRequest,
    RateLimitStatus RateLimitStatus,
    Dictionary<string, object> CustomMetrics);

public record RateLimitStatus(
    bool CanMakeRequest,
    int RequestsRemaining,
    int RequestsPerMinute,
    int RequestsPerHour,
    int RequestsPerDay,
    DateTime? ResetTime,
    TimeSpan? RetryAfter = null);

public record RateLimitRule(
    int RequestsPerMinute,
    int RequestsPerHour,
    int RequestsPerDay,
    RateLimitScope Scope);

public record AuthenticationResult(
    bool IsSuccessful,
    string? AccessToken = null,
    string? RefreshToken = null,
    DateTime? ExpiresAt = null,
    List<string>? Scopes = null,
    string? ErrorMessage = null);

public record CapabilityResult(
    bool IsSuccessful,
    object? Data = null,
    string? ErrorMessage = null,
    TimeSpan ExecutionTime = default,
    Dictionary<string, object>? Metadata = null);

public record ToolDiscoveryResult(
    string Name,
    ToolType Type,
    string Description,
    string BaseUrl,
    AuthenticationMethod AuthMethod,
    List<string> SupportedCapabilities,
    Dictionary<string, object> Metadata);

public record ToolCapabilityDiscovery(
    Guid ConnectionId,
    List<DiscoveredCapability> Capabilities,
    Dictionary<string, object> Metadata);

public record DiscoveredCapability(
    string Name,
    string Description,
    CapabilityType Type,
    bool RequiresAuthentication,
    List<string>? RequiredScopes = null,
    Dictionary<string, object>? Parameters = null);

// Webhook Models
public record WebhookRegistrationRequest(
    Guid ConnectionId,
    string Name,
    string Description,
    string EndpointUrl,
    List<string> EventTypes,
    WebhookSecuritySettings? SecuritySettings = null);

public record WebhookEventPayload(
    string EventType,
    string EventId,
    object Data,
    DateTime Timestamp,
    string? Source = null,
    Dictionary<string, string>? Headers = null);

public record ProcessWebhookRequest(
    Guid WebhookEndpointId,
    string EventType,
    string EventId,
    string Payload,
    Dictionary<string, string>? Headers = null,
    string? Signature = null);

// Sync Models
public record CreateSyncConfigurationRequest(
    Guid ConnectionId,
    string Name,
    string Description,
    SyncDirection Direction,
    SyncFrequency Frequency,
    string EntityType,
    string RemoteEntityType,
    ConflictResolutionStrategy ConflictResolution = ConflictResolutionStrategy.LocalWins,
    SyncSettings? Settings = null,
    FieldMappingConfiguration? FieldMappings = null);

public record SyncExecutionRequest(
    Guid SyncConfigurationId,
    bool ForceFullSync = false,
    Dictionary<string, object>? Parameters = null);

public record SyncExecutionResult(
    Guid ExecutionId,
    bool IsSuccessful,
    int RecordsProcessed,
    int RecordsCreated,
    int RecordsUpdated,
    int RecordsDeleted,
    int ConflictsResolved,
    TimeSpan ExecutionTime,
    string? ErrorMessage = null,
    List<string>? Warnings = null);

// Workflow Models
public record CreateWorkflowTriggerRequest(
    string Name,
    string Description,
    WorkflowTriggerType TriggerType,
    Dictionary<string, object> TriggerConfiguration,
    List<WorkflowAction> Actions);

public record WorkflowAction(
    string Name,
    string ActionType,
    Guid? ConnectionId,
    string? CapabilityName,
    Dictionary<string, object> Parameters,
    Dictionary<string, object>? Conditions = null);

public record WorkflowExecutionContext(
    Guid WorkflowId,
    string TriggerEvent,
    Dictionary<string, object> TriggerData,
    Dictionary<string, object> Variables);

public record WorkflowExecutionResult(
    Guid ExecutionId,
    bool IsSuccessful,
    List<ActionResult> ActionResults,
    TimeSpan ExecutionTime,
    string? ErrorMessage = null);

public record ActionResult(
    string ActionName,
    bool IsSuccessful,
    object? Result = null,
    string? ErrorMessage = null,
    TimeSpan ExecutionTime = default);

// Error Models
public class ToolIntegrationException : Exception
{
    public string? ErrorCode { get; }
    public Dictionary<string, object>? ErrorData { get; }

    public ToolIntegrationException(string message, string? errorCode = null, Dictionary<string, object>? errorData = null)
        : base(message)
    {
        ErrorCode = errorCode;
        ErrorData = errorData;
    }

    public ToolIntegrationException(string message, Exception innerException, string? errorCode = null, Dictionary<string, object>? errorData = null)
        : base(message, innerException)
    {
        ErrorCode = errorCode;
        ErrorData = errorData;
    }
}

public class AuthenticationException : ToolIntegrationException
{
    public AuthenticationException(string message, string? errorCode = null)
        : base(message, errorCode)
    {
    }
}

public class RateLimitExceededException : ToolIntegrationException
{
    public TimeSpan RetryAfter { get; }

    public RateLimitExceededException(string message, TimeSpan retryAfter, string? errorCode = null)
        : base(message, errorCode)
    {
        RetryAfter = retryAfter;
    }
}

public class CapabilityNotSupportedException : ToolIntegrationException
{
    public string CapabilityName { get; }

    public CapabilityNotSupportedException(string capabilityName, string message)
        : base(message)
    {
        CapabilityName = capabilityName;
    }
}