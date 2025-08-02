using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.AgentManagement.Events;

public record McpServerCreatedEvent(Guid ServerId, string Name, string ConnectionString) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record McpServerConnectedEvent(Guid ServerId, string? Version, List<string>? SupportedTools) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record McpServerDisconnectedEvent(Guid ServerId) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record McpServerErrorEvent(Guid ServerId, string Error) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record McpServerEndpointUpdatedEvent(Guid ServerId, string NewConnectionString) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}